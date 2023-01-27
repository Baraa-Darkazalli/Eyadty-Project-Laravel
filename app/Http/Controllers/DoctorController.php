<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Certificate;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Person;
use App\Models\Phone;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    use ApiResponderTrait;

    public function getAllDoctorsReception() //role:
    {
        $doctors = Doctor::join('employees', 'doctors.employee_id', '=', 'employees.id')
            ->join('people', 'employees.person_id', '=', 'people.id')
            ->join('names', 'people.name_id', '=', 'names.id')
            ->select(
                'people.id as id',
                DB::raw("CONCAT(names.first_name,' ',names.last_name) as doctor_name")
            )
            ->get();
        return $doctors;
    }
    public function addDoctor(Request $request) //role:Admin
    {
        $user_data = $request->only(
            'email',
            'username',
            'password',
            'password_confirmation',
        );

        $employee_data = $request->only(
            'first_name',
            'last_name',
            'father_name',
            'gender',
            'birth_date',
            'previous_experience',
            'salary'
        );

        $doctor_data = $request->only(
            'clinic_id',
            'salary_rate',
            'session_duration_id',
            'image'
        );

        //create employee
        $result = \App\Models\Employee::createEmployee($employee_data);

        if ($result['success'] == 0)
            return $this->badRequestResponse($result['errors']);

        $user_data['person_id'] = Employee::find($result['employee_id'])->person->id;

        //crate phones
        if(isset($request->phones)){
            $phones=$request->phones;
            if(empty($phones)) return $this->badRequestResponse(__('msg.phones_are_empty'));
            foreach ($phones as $phone) {
                $person_id = Employee::find($result['employee_id'])->person->id;
                $phonesResult = PhoneController::addPhone($phone, $person_id);
                if ($phonesResult['success'] == 0)
                    return $this->badRequestResponse($phonesResult['message']);
            }
        }

        //create doctor
        $doctor_data['employee_id'] = $result['employee_id'];

        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'clinic_id' => 'required|exists:clinics,id',
            'salary_rate' => 'required|numeric|between:0,1',
            'session_duration_id' => 'required|exists:session_durations,id',
        ];

        $vaildator = Validator::make($doctor_data, $rules);

        if ($vaildator->fails())
            return $this->badRequestResponse($vaildator->errors()->all());

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->extension();
            $path = $image->move('images', $name);
            $path = (string) $path;
        } else {
            $path = null;
        }

        $doctor = new Doctor();
        $doctor->employee_id = $result['employee_id'];
        $doctor->clinic_id = $request->clinic_id;
        $doctor->salary_rate = $request->salary_rate;
        $doctor->session_duration_id = $request->session_duration_id;
        $doctor->image = $path;
        $doctor->save();

        //create user
        $rules = [
            'person_id' => 'required|unique:users',
            'email' => 'max:30|unique:users|email',
            'username' => 'required|max:15|unique:users',
            'password' => 'required|confirmed',
        ];
        $vaildator = Validator::make($user_data, $rules);
        if ($vaildator->fails()) {
            return $this->badRequestResponse($vaildator->errors()->all());
        } else {
            $user = new User();
            $user->person_id = $user_data['person_id'];
            $user->username = $user_data['username'];
            $user->password = bcrypt($user_data['password']);
            $user->email = $user_data['email'] ?? null;
            $user->save();
            $user->attachRole('Doctor');
        }

        //create working hours
        $assignResult = EmployeeController::assignWorkingTimes($request->working_times, $result['employee_id']);
        if ($assignResult['success'] == 0) return $this->badRequestResponse($assignResult['message']);

        //create certificates
        if ($request->has('certificates') && (!empty($request->certificates))) {
            foreach ($request->certificates as $certificate) {
                $data = array_merge($certificate, ['employee_id' => $result['employee_id']]);
                if (!CertificateController::addCertificate($data))
                    return $this->badRequestResponse(__('msg.invalid_value_in_certificates_data'));
            }
        }
        return $this->okResponse(null, __('msg.doctor_created_successfully'));
    }

    public function index() //role:Admin|Patient
    {
        //check if there are no doctors
        if (empty(DB::table('doctors')->count())) {
            return $this->okResponse(null, __('msg.no_doctors_already_exists'));
        }

        //get all doctors
        $data = DB::table('doctors')
            ->join('clinics', 'doctors.clinic_id', '=', 'clinics.id')
            ->join('clinic_names', 'clinics.clinic_name_id', '=', 'clinic_names.id')
            ->join('employees', 'doctors.employee_id', '=', 'employees.id')
            ->join('people', 'people.id', '=', 'employees.person_id')
            ->join('names', 'names.id', '=', 'people.name_id')
            ->select('people.id', DB::raw("CONCAT(names.first_name,' ',names.last_name) AS name"), 'employees.previous_experience', 'clinic_names.name as clinic_name')
            ->get();

        return $this->okResponse($data);
    }

    public function getDoctorsByClinicId(Request $request) //role:Admin|Patient
    {
        return 'h';
        $clinic_id = $request->id;
        $clinic = Clinic::find($clinic_id) ?? false;
        if (!$clinic) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }
        if (empty(Clinic::find($clinic_id)->doctors->count())) {
            return $this->okResponse(null, __('msg.there_are_no_doctors_in_this_clinic'));
        }

        $doctors = Clinic::find($clinic_id)->doctors;
        foreach ($doctors as $doctor) {
            $data[] = [
                'id' => $doctor->employee->person->id,
                'name' => $doctor->employee->person->name->first_name . ' ' . $doctor->employee->person->name->last_name,
                'previous_experience' => $doctor->employee->previous_experience + Carbon::parse($doctor->created_at)->diff(Carbon::now())->y,
            ];
        }

        return $this->okResponse($data);
    }

    public function getSingleDoctor(Request $request) //role: Patient|Admin|Doctor
    {
        $doctor = (Person::find($request->id)->employee->doctor) ?? null;
        if (!$doctor) {
            return $this->badRequestResponse();
        }
        if (empty($doctor->employee->person->phones->count())) {
            $phone_numbers = null;
        }
        foreach ($doctor->employee->person->phones as $phone) {
            $phone_numbers[] = [
                'phone_id' => $phone->id,
                'phone_number' => $phone->phone_number
            ];
        }
        $data = [
            'doctor_name' => $doctor->employee->person->name->first_name . ' ' . $doctor->employee->person->name->last_name,
            'clinic_name' => $doctor->clinic->clinic_name->name,
            'phones' => $phone_numbers,
            'previous_experience' => $doctor->employee->previous_experience + Carbon::parse($doctor->created_at)->diff(Carbon::now())->y,
            'patient_count' => Appointment::where('doctor_id', '=', $doctor->id)->distinct('patient_id')->count('patient_id'),
            'appointment_count' => Appointment::where('doctor_id', '=', $doctor->id)->count(),
            'age' => Carbon::parse($doctor->employee->person->birth_date)->diff(Carbon::now())->y,
            'slot_time' => $doctor->session_duration->session_duration,
            'gender' => $doctor->employee->person->gender,
            'image' => $doctor->image,
        ];

        return $this->okResponse($data);
    }

    public function search(Request $request)
    {
        if (isset($request->clinic_id)) {
            $doctors = Doctor::join('employees', 'employee_id', 'employees.id')
                ->join('people', 'people.id', 'employees.person_id')
                ->join('names', 'people.name_id', 'names.id')
                ->join('clinics', 'clinics.id', 'doctors.clinic_id')
                ->join('clinic_names', 'clinic_names.id', 'clinics.clinic_name_id')
                ->select('people.id AS doctor_id', DB::raw("CONCAT(names.first_name,' ',names.last_name) as name"), 'employees.previous_experience', 'clinic_names.name AS clinic_name')
                ->where([['doctors.clinic_id', $request->clinic_id], ['names.first_name', 'LIKE', '%' . $request->input . '%']])
                ->orWhere([['doctors.clinic_id', $request->clinic_id], ['names.last_name', 'LIKE', '%' . $request->input . '%']])
                ->get();
        } else {
            $doctors = Doctor::join('employees', 'employee_id', 'employees.id')
                ->join('people', 'people.id', 'employees.person_id')
                ->join('names', 'people.name_id', 'names.id')
                ->join('clinics', 'clinics.id', 'doctors.clinic_id')
                ->join('clinic_names', 'clinic_names.id', 'clinics.clinic_name_id')
                ->select('people.id AS doctor_id', DB::raw("CONCAT(names.first_name,' ',names.last_name) as name"), 'employees.previous_experience', 'clinic_names.name AS clinic_name')->where('names.first_name', 'LIKE', '%' . $request->input . '%')
                ->orWhere('names.last_name', 'LIKE', '%' . $request->input . '%')
                ->get();
        }
        if (count($doctors) > 0)
            return $this->okResponse($doctors);
        else {
            return $this->okResponse(null, __('msg.input_not_found'));
        }
    }
}
