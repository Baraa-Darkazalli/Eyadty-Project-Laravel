<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;

// use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    use ApiResponderTrait,HelperTrait;

    public function getAllPatientsReception()
    {
        $patients=Patient::join('people','patients.person_id','people.id')
        ->join('names','people.name_id','names.id')
        ->select('people.id as id',
        DB::raw("CONCAT(names.first_name,' ',names.last_name) as patient_name")
        )
        ->get();
        return $patients;
    }
    public function addPatient(Request $request)//role: admin|Reception
    {
        $person_data = $request->only(
            'first_name',
            'last_name',
            'father_name',
            'gender',
            'birth_date',
            'phone',
        );
        $result = \App\Models\Person::createPerson($person_data);
        if ($result['success'] == 0) {
            return $this->badRequestResponse($result['errors']);
        } else {
            $pateint_data = $request->only(
                'blood_type_id',
                'weight',
                'height',
                'father_name'
            );
            $rules = [
                'father_name' => 'required',
            ];
            $validator = Validator::make($pateint_data, $rules);
            if ($validator->fails()) {
                return $this->badRequestResponse($validator->errors()->all());
            } else {
                //crate phone
                if(isset($request->phone)){
                    $phone=$request->only('phone');
                    $person_id=$result['person_id'];
                        $phonesResult=PhoneController::addPhone($phone,$person_id);
                        if ($phonesResult['success'] == 0)
                            return $this->badRequestResponse($phonesResult['message']);
                    }

                //create patient
                $person_id = $result['person_id'];
                $patient = new \App\Models\Patient();
                $patient->person_id = $person_id;
                $patient->blood_type_id = $request->blood_type_id;
                $patient->weight =
                    isset($request->weight) ?
                        $request->weight :
                        null;

                $patient->height =
                    isset($request->height) ?
                        $request->height :
                        null;

                $patient->save();
                $data = [
                    'id' => $patient->person->id
                ];

                return $this->createdResponse($data, __('msg.patient_created_successfully'));
            }
        }
    }

    public function getAllPatients($type = null)// role:Admin|Reception
    {
        //check if there are no patients
        if (empty(Patient::count())) {
            return $this->okResponse(null, __('msg.there_are_no_patients'));
        }

        //loop for each patient
        foreach (Patient::all() as $patient) {
            $data[] = [
                'id' => $patient->person->id,
                'name' => $patient->person->name->first_name.' '.$patient->person->name->last_name,
                'age' => Carbon::parse($patient->person->birth_date)->diff(Carbon::now())->y,
                'phone_number' => ($patient->person->phones->first()->phone_number) ?? null,
                'appointments_count' => Appointment::where('patient_id', '=', $patient->id)->count(),
            ];
        }
        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }
        return $this->okResponse($data);
    }

    public function getSinglePatient(Request $request)//role Doctor|Admin|Reception
    {
        $patient = (Person::find($request->id)->patient) ?? false;
        if (! $patient) {
            return $this->badRequestResponse();
        }

        //patient allergies
        if (empty($patient->allergies->count())) {
            $patient_allergies = null;
        } else {
            foreach ($patient->allergies as $allergy) {
                $patient_allergies[] = $allergy->name;
            }
        }

        //patient diseases
        if (empty($patient->diseases->count())) {
            $patient_diseases = null;
        } else {
            foreach ($patient->diseases as $disease) {
                $patient_diseases[] = $disease->name;
            }
        }

        $data = [
            'appointment_count' => Appointment::where('patient_id', '=', $patient->id)->count(),
            'email' => $patient->person->user->email??null,//مشان اذا ما كان عندو حساب
            'gender' => $patient->person->gender,
            'birth_date' => $patient->person->birth_date,
            'first_name' => $patient->person->name->first_name,
            'father_name' => $patient->person->name->father_name,
            'last_name' => $patient->person->name->last_name,
            'phone_number' => ($patient->person->phones->first()->phone_number) ?? null,
            'age' => Carbon::parse($patient->person->birth_date)->diff(Carbon::now())->y,
            'height' => $patient->height,
            'weight' => $patient->weight,
            'blood_group' => $patient->blood_types->name,
            'allergies' => $patient_allergies,
            'diseases' => $patient_diseases,
        ];


        return $this->okResponse($data);
    }

    public function getMyPatients($type = null)// role:Doctor
    {
        $doctor_id = (User::find(auth()->user()->id)->person->employee->doctor->id) ?? false;
        if (! $doctor_id) {
            return $this->badRequestResponse();
        }

        //check if there are no patients
        if (empty(Appointment::where('doctor_id', '=', $doctor_id)->count())) {
            return $this->okResponse(null, __('msg.there_are_no_patients'));
        }

        //loop for each patient
        $appointments = Appointment::where('doctor_id', '=', $doctor_id)->get();
        foreach ($appointments as $appointment) {
            $patient=$appointment->patient;
            $data[]=[
                'id'=>$patient->person->id,
                'name'=>$patient->person->name->first_name.' '.$patient->person->name->last_name,
                'age'=>Carbon::parse($patient->person->birth_date)->diff(Carbon::now())->y,
                'phone_number'=>($patient->person->phones->first()->phone_number)??null,
                'appointments_count'=>Appointment::where('patient_id','=',$patient->id)->count(),
            ];
        }
        $data = collect($data)->unique()->values();
        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data->toArray());
        }

        return $this->okResponse($data);
    }
}
