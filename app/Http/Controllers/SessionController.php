<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Person;
use App\Models\Prescription;
use App\Models\Session;
use App\Models\SessionCalculation;
use App\Models\SessionDuration;
use App\Models\User;
use App\Models\Waiting;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    use ApiResponderTrait;
    //GET


    public function getSessionDurationMenu()
    {
        $session_durations=SessionDuration::query()->select('id','session_duration')->get();
        if(empty($session_durations->count()))return $this->okResponse(null,'There are no session duratinos');
        return $this->okResponse($session_durations);

    }

    public function openSession(Request $request)//role:Doctor
    {
        $waiting = (Waiting::find($request->id)) ?? false;
        //check id exists
        if (! $waiting||$waiting->finished==1) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }
        //set finish waiting
        $waiting->finished=1;
        $waiting->save();

        //patient name
        $patient_first_name = $waiting->patient->person->name->first_name;
        $patient_father_name = ($waiting->patient->person->name->father_name) ?? '';
        $patient_last_name = $waiting->patient->person->name->last_name;

        //doctor name
        $doctor_first_name = $waiting->doctor->employee->person->name->first_name;
        $doctor_last_name = $waiting->doctor->employee->person->name->last_name;

        $data = [
            'patient_name' => $patient_first_name.' '.$patient_father_name.' '.$patient_last_name,
            'contact_number' => ($waiting->patient->person->phones->first()->phone_number) ?? null,
            'gender' => $waiting->patient->person->gender,
            'age' => Carbon::parse($waiting->patient->person->birth_date)->diff(Carbon::now())->y,
            'session_date' => Carbon::now()->format('Y-m-d'),
            'session_time' => Carbon::now()->format('H:i'),
            'doctor_name' => $doctor_first_name.' '.$doctor_last_name,
            'clinic_name' => $waiting->doctor->clinic->clinic_name->name,
            'session_price' => $waiting->doctor->clinic->session_price,
        ];

        return $this->okResponse($data);
    }

    public function createSession(Request $request)//role:Doctor
    {
        $waiting = Waiting::find($request->waiting_id) ?? false;
        if (! $waiting||$waiting->finished==1) {
            return $this->badRequestResponse();
        }

        $waiting->finished=1;
        $waiting->save();

        $data=$request->only('title');
        $data['doctor_id']=auth()->user()->person->employee->doctor->id;
        $validator = Validator::make($data, [
            'title' => 'required',
            'doctor_id'=>"in:{$waiting->doctor_id}"
        ]);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }


        $session = new Session();
        $session->title = $request->title;
        $session->description = ($request->description) ?? null;
        $session->is_review = ($request->is_review) ?? false;
        $session->session_date=Carbon::parse()->format('Y-m-d');
        $session->session_time=Carbon::parse()->format('H:i:s');
        $session->waiting_id=$waiting->id;
        if ($session->is_review == true) {
            if (! isset($request->previous_session_id)) {
                return $this->badRequestResponse();
            }
            if (Session::query()->where('id',$request->previous_session_id)->doesntExist()) {
                return $this->badRequestResponse(__('msg.invalid_previous_session'));
            }
            $session->previous_session_id = $request->previous_session_id;
        }
        $session->save();

        $session_calc = new SessionCalculation();
        $session_calc->session_id = $session->id;
        $session_calc->save();

        if ($request->hasAny('medicines', 'medical_analysis')) {
            $prescription = new Prescription();
            $prescription->session_id = $session->id;
            $prescription->save();
            if ($request->has('medicines')) {
                if (! MedicineController::AddMedicine($prescription->id, $request->medicines)) {
                    return $this->badRequestResponse(__('you_entered_invalid_medicine_data'));
                }
            }

            if ($request->has('medical_analysis')) {
                if (! MedicalAnalysisController::AddMedicalAnalysis($prescription->id, $request->medical_analysis)) {
                    return $this->badRequestResponse(__('you_entered_invalid_medical_analysis_data'));
                }
            }

        }
        if ($request->has('invoice')) {
            if (! TreatmentsController::AddExtraTreatment($session_calc->id, $request->invoice)) {
                return $this->badRequestResponse(__('you_entered_invalid_medical_analysis_data'));
            }
        }
        if(isset($waiting->appointment))
        {
            $appointment=$waiting->appointment;
            $appointment->update(['appointment_statue_id'=>3]);
        }

        return $this->okResponse(__('msg.session_created_successfully'));
    }

    public function getSessionsTitle(Request $request)//role:Doctor
    {
        $waiting_id = (Waiting::find($request->waiting_id)) ?? false;
        if (! $waiting_id) {
            return $this->badRequestResponse();
        }
        $patient_id=Waiting::find($request->waiting_id)->patient->id??false;
        if (! $patient_id) {
            return $this->badRequestResponse();
        }
        if (empty(Waiting::where([['patient_id', $patient_id],['finished','=',true]])->count())) {
            return $this->okResponse(null, __('msg.there_ara_no_previous_sessions'));
        }

        return $this->okResponse(Session::select('sessions.id', 'title')->join('waitings','waitings.id','sessions.waiting_id')
                    ->where('waitings.patient_id', $patient_id)->get());
    }
}
