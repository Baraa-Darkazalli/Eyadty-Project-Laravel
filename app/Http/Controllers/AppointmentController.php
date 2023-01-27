<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Person;
use App\Models\Session;
use App\Models\User;
use App\Rules\BetweenTwoTimesRule;
use App\Rules\MultipleUniqueRule;
use App\Rules\multipliDurationRule;
use App\Traits\ApiResponderTrait;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Time;

class AppointmentController extends Controller
{
    use ApiResponderTrait, HelperTrait;

    public function book(Request $request)
    {
        $init_data = $request->only(
            'is_review',
            'appointment_time',
            'appointment_date',
            'doctor_id',
            'patient_id'
        );
        $doctor_id = Person::find($init_data['doctor_id'])->employee->doctor->id ?? 'false';
        // $patient_id=Person::find($data['patient_id'])->patient->id??'false';
        $init_data['doctor_id'] = $doctor_id;
        $next_month = Carbon::parse()->addMonth()->format('Y-m-d');
        $init_rules = [
            'appointment_time' => 'required|date_format:H:i',
            'appointment_date' => "required|date_format:Y-m-d|before_or_equal:{$next_month}",
            'doctor_id' => 'required|not_in:false',
            'is_review' => 'required|in:1,0',
        ];
        $init_messages = [
            'doctor_id.not_in' => __('msg.you_entered_invalid_doctor_id'),
        ];
        if (auth()->user()->hasRole('Reception')) {
            $reception_id = auth()->user()->person->employee->reception->id;
            $patient_id = Person::find($init_data['patient_id'])->patient->id ?? 'false';
            $init_data['patient_id'] = $patient_id;
            $init_rules['patient_id'] = 'not_in:false|required';
            $init_messages['patient_id.not_in'] = __('msg.you_entered_invalid_patient_id');
        } else if (auth()->user()->hasRole('Patient')) {
            $patient_id = auth()->user()->person->patient->id;
        } else {
            return $this->forbiddenResponse(__('msg.you_can_not_add_appointment'));
        }
        $calnceld_appointments = Appointment::where([['patient_id', '=', $patient_id], ['appointment_statue_id', '=', 4]])->count();
        if ($calnceld_appointments > 2)
            return $this->badRequestResponse('cannot_book_because_cancel');
        $init_validate = Validator::make($init_data, $init_rules, $init_messages);
        if ($init_validate->fails()) {
            return $this->badRequestResponse($init_validate->errors()->all());
        }

        $data_validation['doctor_appointments_conflict'] = [
            'doctor_id' => $doctor_id,
            'appointment_date' => $init_data['appointment_date'],
            'appointment_time' => $init_data['appointment_time'],
            'appointment_statue_id' => 1
        ];
        $data_validation['patient_appointments_conflict'] = [
            'patient_id' => $patient_id,
            'appointment_date' => $init_data['appointment_date'],
            'appointment_time' => $init_data['appointment_time'],
            'appointment_statue_id' => 1
        ];

        $day = Carbon::parse($init_data['appointment_date'])->format('l');
        $doctor_employee_id = Doctor::find($doctor_id)->employee->id;

        $req = new Request();
        $req->replace(['date' => $init_data['appointment_date'], 'employee_id' => $doctor_employee_id]);
        $times = (new EmployeeController)->getWorkingTimeInDay($req);
        // return $times->status();
        $status = $times->status();
        if (!in_array($status, [200, 201])) {
            return $times;
        }

        $times = json_decode($times->getContent(), true)['data'];
        $doctor_duration = (int) Doctor::find($doctor_id)->session_duration->session_duration;
        $today_date = Carbon::now()->format('Y-m-d');
        $appointments_in_today = Appointment::where([['patient_id', '=', $patient_id]])->whereDate('created_at', '=', Carbon::now())->count();
        $appointments_in_any_day = Appointment::where([['patient_id', '=', $patient_id], ['appointment_date', '=', $init_data['appointment_date']]])->count();
        $data_validation['appointments_in_any_day'] = $appointments_in_any_day;
        $data_validation['appointments_in_today'] = $appointments_in_today;
        $rules =
            [
                'doctor_appointments_conflict' => new MultipleUniqueRule('appointments', 'there is an another appointment'),
                'patient_appointments_conflict' => new MultipleUniqueRule('appointments', 'you have another appointment'),
                'appointment_time' => [new multipliDurationRule($doctor_duration), new BetweenTwoTimesRule($doctor_duration, $times)],
                'appointments_in_any_day' => 'numeric|max:3',
                'appointments_in_today' => 'numeric|max:3',
            ];
        $validator = Validator::make($data_validation, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $app = new Appointment();
        $app->patient_id = $patient_id;
        $app->reception_id = $reception_id ?? null;
        $app->doctor_id = $doctor_id;
        $app->appointment_date = $init_data['appointment_date'];
        $app->appointment_time = $init_data['appointment_time'];
        $app->is_review = $init_data['is_review'];
        $app->appointment_statue_id = 1;
        $app->save();

        return $this->createdResponse($app);
    }

    public function getCalander($type) //role:Patient|Doctor
    {
        $user_id = auth()->user()->id;
        $today = Carbon::today();
        switch ($type) {
            case 'Patient':
                $patient_id = User::find($user_id)->person->patient->id;
                $appointments = Appointment::where([['patient_id', '=', $patient_id], ['appointment_date', '>=', $today]])->get();
                break;

            case 'Doctor':
                $doctor_id = User::find($user_id)->person->employee->doctor->id;
                $appointments = Appointment::where([['doctor_id', '=', $doctor_id], ['appointment_date', '>=', $today]])->get();
                break;

            default:
                return $this->badRequestResponse();
                break;
        }

        //check if there are no appointments
        if (empty($appointments->count())) {
            return $this->okResponse(null, __('msg.there_are_no_appointments_for_you'));
        }

        foreach ($appointments as $appointment) {
            $data[] = $appointment->appointment_date;
        }

        return $this->okResponse($data);
    }

    public function getEvents(Request $request, $type) //role:Patient|Doctor
    {
        $date = $request->date;
        if (!isset($date)) {
            return $this->badRequestResponse();
        }

        switch ($type) {
            case 'Patient':
                $patient_id = User::find(auth()->user()->id)->person->patient->id;
                $appointments = Appointment::where([['patient_id', '=', $patient_id], ['appointment_date', '=', $date]])->get();
                break;

            case 'Doctor':
                $doctor_id = User::find(auth()->user()->id)->person->employee->doctor->id;
                $appointments = Appointment::where([['doctor_id', '=', $doctor_id], ['appointment_date', '=', $date]])->get();
                break;

            default:
                return $this->badRequestResponse();
                break;
        }

        //check if there are no appointments
        if (empty($appointments->count())) {
            return $this->okResponse(null, __('msg.there_are_no_appointments_in_this_date'));
        }

        foreach ($appointments as $appointment) {
            switch ($type) {
                case 'Patient':
                    //doctor name
                    $name = $appointment->doctor->employee->person->name->first_name . ' ' . $appointment->doctor->employee->person->name->last_name;
                    break;

                case 'Doctor':
                    //patient name
                    $name = $appointment->patient->person->name->first_name . ' ' . $appointment->patient->person->name->last_name;
                    break;
            }
            $data[] = [
                'id' => $appointment->id,
                'name' => $name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'is_review' => $appointment->is_review,
                'statue' => $appointment->appointment_statue->name,
            ];
        }

        return $this->okResponse($data);
    }

    public function getMyAppointmentsPatient() //role:Patient
    {
        $user_id = auth()->user()->id;
        $patient = User::find($user_id)->person->patient;

        //check if there are no appoinmtents
        if (empty($patient->appointments->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_appointments'));
        }
        $appointments_count = Appointment::where([
            ['patient_id', '=', auth()->user()->person->patient->id],
            ['appointment_statue_id', '=', 2]
        ])
            ->whereMonth('appointment_date', Carbon::parse()->month)
            ->count();
        if ($appointments_count > 2)
            $can_cancel = false;

        //get appointments
        foreach ($patient->appointments as $appointment) {
            if ($appointment->appointment_statue->name == 'Done') {
                continue;
            }

            $data[] = [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctor->employee->person->name->first_name . ' ' . $appointment->doctor->employee->person->name->last_name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'is_review' => $appointment->is_review,
                'statue' => $appointment->appointment_statue->name,
                'can_cancel' => ($appointment->appointment_statue->name == 'Canceled') ? false : $can_cancel??false,
            ];
        }

        //check if there are no appointments
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_appointments'));
        }

        return $this->okResponse($data);
    }

    public function getMyAppointmentsDoctor($type = null) //role:Doctor
    {
        $user_id = auth()->user()->id;
        $doctor = User::find($user_id)->person->employee->doctor;

        //check if there are no appoinmtents
        if (empty($doctor->appointments->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_appointments'));
        }

        //get appointments
        foreach ($doctor->appointments as $appointment) {
            if ($appointment->appointment_statue->name == 'Done') {
                continue;
            }

            $data[] = [
                'patient_id' => $appointment->patient->person->id,
                'patient_name' => $appointment->patient->person->name->first_name . ' ' . $appointment->patient->person->name->last_name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'is_review' => $appointment->is_review,
                'statue' => $appointment->appointment_statue->name,
            ];
        }

        //check if there are no appointments
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_appointments'));
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function getMyReportsPatient() //role:Patient
    {
        $user_id = auth()->user()->id;
        $patient = User::find($user_id)->person->patient;

        //check if there are no reports
        if (empty($patient->waitings->where('finished', '1', true)->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        //get reports

        foreach ($patient->waitings->where('finished', '1', true) as $waiting) {
            $data[] = [
                'id' => $waiting->session->id,
                'doctor_name' => $waiting->doctor->employee->person->name->first_name . ' ' . $waiting->doctor->employee->person->name->last_name,
                'date' => $waiting->session->session_date,
                'time' => $waiting->session->session_time,
                'is_review' => $waiting->session->is_review,
                'statue' => 'Done',
            ];
        }

        //check if there are no reports
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        return $this->okResponse($data);
    }

    public function getMyReportsDoctor($type = null) //role:Doctor
    {
        $user_id = auth()->user()->id;
        $doctor = User::find($user_id)->person->employee->doctor;

        //check if there are no reports
        if (empty($doctor->waitings->where('finished', '1', true)->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        //get reports
        foreach ($doctor->waiting->where('finished', '1', true) as $waiting) {
            $data[] = [
                'id' => $waiting->session->id,
                'patient_name' => $waiting->patient->person->name->first_name . ' ' . $waiting->patient->person->name->last_name,
                'date' => $waiting->session_date,
                'time' => $waiting->session_time,
                'is_review' => $waiting->is_review,
                'statue' => 'Done'
            ];
        }

        //check if there are no reports
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function getAppointmentsByPatientId(Request $request, $type = null) //role: Admin|Reception|Doctor
    {
        $person = Person::find($request->id);
        $patient = ($person->patient) ?? (false);

        //check if id exists
        if (!$patient) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        //check if there are no appoinmtents
        if (empty($patient->appointments->count())) {
            return $this->okResponse(null, __('msg.this_patient_does_not_has_any_appointments'));
        }

        //get appointments
        foreach ($patient->appointments as $appointment) {
            if ($appointment->appointment_statue->name == 'Done') {
                continue;
            }

            $data[] = [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctor->employee->person->name->first_name . ' ' . $appointment->doctor->employee->person->name->last_name,
                'clinic_name' => $appointment->doctor->clinic->clinic_name->name,
                'patient_id' => $appointment->patient->person->id,
                'patient_name' => $appointment->patient->person->name->first_name . ' ' . $appointment->patient->person->name->last_name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'is_review' => $appointment->is_review,
                'statue' => $appointment->appointment_statue->name,
            ];
        }

        //check if there are no appointments
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.this_patient_does_not_has_any_appointments'));
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate')
            $data = $this->paginate($data);

        return $this->okResponse($data);
    }

    public function getReportsByPatientId(Request $request, $type = null) //role: Admin|Reception
    {
        $person = Person::find($request->id);
        $patient = ($person->patient) ?? (false);

        //check if id exists
        if (!$patient) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        //check if there are no appoinmtents
        if (empty($patient->waitings->where('finished', '1', true)->count())) {
            return $this->okResponse(null, __('msg.this_patient_does_not_has_any_reports'));
        }

        //get appointments
        foreach ($patient->waitings->where('finished', '1', true)->get() as $waiting) {
            $data[] = [
                'id' => $waiting->session->id,
                'doctor_name' => $waiting->doctor->employee->person->name->first_name . ' ' . $waiting->doctor->employee->person->name->last_name,
                'clinic_name' => $waiting->doctor->clinic->clinic_name->name,
                'patient_id' => $waiting->patient->person->id,
                'patient_name' => $waiting->patient->person->name->first_name . ' ' . $waiting->patient->person->name->last_name,
                'date' => $waiting->session->session_date,
                'time' => $waiting->session->session_time,
                'is_review' => $waiting->session->is_review,
                'statue' => "Done",
            ];
        }

        //check if there are no reports
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.this_patient_does_not_has_any_reports'));
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate')
            $data = $this->paginate($data);

        return $this->okResponse($data);
    }

    public function getSingleReport(Request $request) //role: all
    {
        $report_id = $request->id;
        $report = (Session::find($report_id)) ?? false;
        //check id exists
        if (!$report) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        //get doctor phones
        if (empty($report->waiting->doctor->employee->person->phones->count())) {
            $doctor_phones = null;
        } else {
            foreach ($report->waiting->doctor->employee->person->phones as $phone) {
                $doctor_phones[] = $phone->phone_number;
            }
        }
        if (isset($report->prescription)) {
            //get medications
            if (empty($report->prescription->medicines->count())) {
                $medications = null;
            } else {
                foreach ($report->prescription->medicines as $medicine) {
                    $medications[] = [
                        'name' => $medicine->medical_name->name,
                        'number_of_doses' => $medicine->num_of_doses,
                        'dose_description' => $medicine->dose_description,
                        'number_of_cans' => $medicine->num_of_pieces,
                    ];
                }
            }

            //get medical analyses
            if (empty($report->prescription->medical_analyses->count())) {
                $medical_analyses = null;
            } else {
                foreach ($report->prescription->medical_analyses as $medical_analyse) {
                    $medical_analyses[] = [
                        'name' => $medical_analyse->medical_analysis_name->name,
                        'description' => $medical_analyse->description,
                    ];
                }
            }
        }

        //get extra treatments
        $extra_treatmente[] = [
            'title' => 'session_price',
            'price' => $report->waiting->doctor->clinic->session_price ?? 0,
        ];
        $total_price = $report->waiting->doctor->clinic->session_price ?? 0;
        $extra_treatments = $report->session_calculation->extra_treatments ?? false;
        if ($extra_treatments) {
            foreach ($report->session_calculation->extra_treatments as $treatment) {
                $extra_treatmente[] = [
                    'title' => $treatment->treatment_name,
                    'price' => $treatment->item_price + $treatment->treatment_price,
                ];
                $total_price += $treatment->item_price + $treatment->treatment_price;
            }
        }

        //get data
        $data = [
            'session_details' => [
                'id' => $report->id,
                'is_review' => $report->is_review,
                'session_date' => $report->session_date
            ],

            'patient_details' => [
                'name' => $report->waiting->patient->person->name->first_name . ' ' . $report->waiting->patient->person->name->last_name,
                'phones' => ($report->waiting->patient->person->phones->first()->phone_number) ?? null,
                'email' => $report->waiting->patient->person->user->email ?? null
            ],
            'doctor_details' => [
                'name' => $report->waiting->doctor->employee->person->name->first_name . ' ' . $report->waiting->doctor->employee->person->name->last_name,
                'clinic_name' => $report->waiting->doctor->clinic->clinic_name->name,
                'phones' => $doctor_phones,
                'email' => $report->waiting->doctor->employee->person->user->email ?? null
            ],
            'prescription_details' => [
                'title' => $report->title,
                'description' => $report->description,
            ],
            'medications' => $medications ?? null,
            'medical_analyses' => $medical_analyses ?? null,
            'invoice_details' => [
                'is_paid' => $report->session_calculation->is_paid,
                'invoice_table' => $extra_treatmente,
                'total_price' => $total_price,
            ],
        ];

        return $this->okResponse($data);
    }

    public function cancelAppointment(Request $request) //role:Patient|Reception
    {
        if (auth()->user()->hasRole('Patient')) {
            $appointments_count = Appointment::where([
                ['patient_id', '=', auth()->user()->person->patient->id],
                ['appointment_statue_id', '=', 2]
            ])
                ->whereMonth('appointment_date', Carbon::parse()->month)
                ->count();
            if ($appointments_count > 2)
                return $this->badRequestResponse(__('msg.cannot_cancel'));
        } else {


            $appointment = (Appointment::find($request->id)) ?? false;
            if (!$appointment) {
                return $this->badRequestResponse(__('msg.this_is_failed_id'));
            }
            if ($appointment->appointment_statue->name != 'Pending') {
                return $this->badRequestResponse(__('msg.this_is_failed_id'));
            }

            //Canceled appointment
            $appointment->appointment_statue_id = 2;
            $appointment->save();

            //Send Notification to patient
            $title = "Appointment canceled";
            $content = 'Appointment date: ' . $appointment->appointment_date;
            $data = [
                'receiver_id' => $appointment->patient->person->user->id,
                'title' => $title,
                'content' => $content
            ];
            if (Notification::sendNotification($data)) {
                return $this->okResponse(__('msg.appoinment_canceled_successfully'));
            }
        }
    }

    public function getAvailableApps(Request $request)
    {
        $today = Carbon::parse()->now()->format('Y-m-d');
        $data = $request->only(
            'date',
            'doctor_id'
        );
        $doctor_id = Person::find($request->doctor_id)->employee->doctor->id ?? 'false';
        $data['doctor_id'] = $doctor_id;
        $after_month_date = Carbon::parse($today)->addMonth()->format('Y-m-d');
        $rules =
            [
                'doctor_id' => 'required|not_in:false',
                // 'date' => "required|date_format:Y-m-d|after_or_equal:{$today}|before_or_equal:{$after_month_date}",
            ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }
        $req = new Request();
        $req->replace(['date' => $data['date'], 'employee_id' => $request->doctor_id]);
        $times = (new EmployeeController)->getWorkingTimeInDay($req);
        if ($times->status() == 204) {
            return $this->noContent();
        }

        $doctor_duration = Carbon::parse(Doctor::find($doctor_id)->session_duration->session_duration)->minute;
        $times = json_decode($times->getContent(), true)['data'];
        $arr = [];
        foreach ($times as $time) {
            $start = Carbon::parse($time['start']);
            $end = Carbon::parse($time['end']);
            while (!$start->eq($end)) {
                if (DB::table('appointments')->where('appointment_time', '=', $start->format('H:i'))->whereDate('appointment_date', '=', $data['date'])->where('doctor_id', '=', $doctor_id)->whereIn('appointment_statue_id', [1, 3])->exists()) {
                    $start->addMinutes($doctor_duration);
                    continue;
                }
                $arr[] = $start->format('H:i');
                $start->addMinutes($doctor_duration);
            }
        }

        $arr = collect($arr);
        $arr = $arr->sort();
        $arr = $arr->values();
        if ($arr->count() == 0) {
            return $this->noContent("there are no appointments in this day {$data['date']}");
        }

        return $this->okResponse($arr);
    }
    public function getTodayReports($type = null) //role:Doctor
    {
        $doctor = User::find(auth()->user()->id)->person->employee->doctor;
        $today = Carbon::today()->format('Y-m-d');
        //check if there are no appoinmtents
        if (empty($doctor->sessions->where('session_date', '=', $today)->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        $sessions = $doctor->sessions->where('session_date', '=', $today);
        foreach ($sessions as $session) {
            $data[] = [
                'id' => $session->id,
                'date' => $session->created_at,
                'time' => $session->created_at,
                'is_review' => $session->is_review,
                'statue' => "Done",
            ];
        }
        //check if there are no reports
        if (!isset($data)) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_report'));
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function getTodayAppointments($type = null) //role:Reception
    {
        $today = Carbon::today()->format('Y-m-d');

        //check if there are no appoinmtents
        if (empty(Appointment::where('appointment_date', '=', $today)->count())) {
            return $this->okResponse(null, __('msg.there_are_no_appointments'));
        }

        $appointments = Appointment::where('appointment_date', '=', $today)->get();
        foreach ($appointments as $appointment) {
            $waiting = $appointment->waiting ?? false;
            if ($waiting != false) continue;
            $data[] = [
                'appointment_id' => $appointment->id,
                'doctor_name' => $appointment->doctor->employee->person->name->first_name . ' ' . $appointment->doctor->employee->person->name->last_name,
                'patient_id' => $appointment->patient->person->id,
                'patient_name' => $appointment->patient->person->name->first_name . ' ' . $appointment->patient->person->name->last_name,
                'clinic_name' => $appointment->doctor->clinic->clinic_name->name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'is_review' => $appointment->is_review,
                'statue' => $appointment->appointment_statue->name,
            ];
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function getAvailableAppointmetsDates(Request $request)
    {
        $doctor_id = Person::find($request->doctor_id)->employee->doctor->id ?? 'false';
        if ($doctor_id == 'false' || !isset($doctor_id)) {
            return $this->badRequestResponse(['doctor_id' => 'invalid input']);
        }
        $today = Carbon::parse()->now();
        $after_month_date = Carbon::parse($today)->addMonth();
        $arr = [];
        while (!$today->eq($after_month_date)) {
            $req = new Request();
            $req->replace(['date' => $today->format('Y-m-d'), 'doctor_id' => Doctor::find($doctor_id)->employee->person->id]);
            $result = (new AppointmentController)->getAvailableApps($req);
            if ($result->status() == 204) {
                $today->addDay();
                continue;
            } else {
                $arr[] = $today->format('Y-m-d');
                $today->addDay();
            }
        }
        if (empty($arr)) {
            return $this->noContent(__('msg.there_are_no_appointments_for_a_month'));
        }

        return $this->okResponse($arr);
    }
}
