<?php

namespace App\Http\Controllers;

use App\Events\AddEmergencyPatient;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Person;
use App\Models\User;
use App\Models\Waiting;
use App\Traits\ApiResponderTrait;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Extension\Table\Table;
use phpDocumentor\Reflection\Types\This;

use function PHPUnit\Framework\returnSelf;

class WaitingController extends Controller
{
    use ApiResponderTrait,HelperTrait;

    public function addFromAppointment(Request $request)
    {
        //find appointment model
        $appointment=Appointment::find($request->appointment_id);
        //check if appointment_id if it's invalid or didn't enter
        if(!$appointment)
            return $this->badRequestResponse('you entered invalid appointment_id');

        $appointment_date=$appointment->appointment_date;
        $appointment_statue_id=$appointment->appointment_statue->id;
        $data=[
            'appointment_date'=>$appointment_date,
            'appointment_statue'=>$appointment_statue_id
        ];
        $rules=[
            'appointment_date'=>'date_equals:today',
            'appointment_statue'=>'in:1'
        ];
        //check if appointment it's not pending or its date doesn't today
        $validator=Validator::make($data,$rules);
        if($validator->fails())
            return $this->badRequestResponse($validator->errors()->all());
        //get data
        $patient_id=$appointment->patient->id;
        $doctor_id=$appointment->doctor->id;

        //check if patinet exists in waiting
        if(Waiting::where([['patient_id','=',$patient_id],['finished','=',false]])->count()>0)
            return $this->badRequestResponse(__('msg.patient_already_exists_in_waiting'));

        //save to database
        $waiting=new Waiting();
        $waiting->patient_id=$patient_id;
        $waiting->doctor_id=$doctor_id;
        $waiting->priority=0;
        $waiting->appointment_id=$request->appointment_id;
        $waiting->save();

        return $this->createdResponse($waiting);
    }

    public function addEmergencies(Request $request)
    {
        $data = $request->only('patient_id', 'doctor_id');
        $patient_id = Person::find($request->patient_id)->patient->id ?? 'false';
        $doctor_id = Person::find($request->doctor_id)->employee->doctor->id ?? 'false';
        $data['patient_id'] = $patient_id;
        $data['doctor_id'] = $doctor_id;
        $rules =
        [
            'doctor_id' => 'not_in:false|required',
            'patient_id' => 'not_in:false|required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        if(Waiting::where([['patient_id','=',$patient_id],['finished','=',false]])->count()>0)
            return $this->badRequestResponse(__('msg.patient_already_exists_in_waiting'));
        $waiting = new Waiting();
        $waiting->patient_id = $patient_id;
        $waiting->doctor_id = $doctor_id;
        $waiting->priority = 1;
        $waiting->save();
        event(new AddEmergencyPatient($doctor_id,Carbon::today()->format('Y-m-d') ));

        return $this->createdResponse(__('msg.patient_added_to_waiting'));
    }

    public function getDoctorWaitings(Request $request,$type=false)
    {
        if(isset($request->doctor_id)){
            $doctor_id = Person::find($request->doctor_id)->employee->doctor->id ?? 'false';
        }
        else $doctor_id=auth()->user()->person->employee->doctor->id??'false';
        if ($doctor_id == 'false') {
            return $this->badRequestResponse();
        }
        // return $doctor_id;
        $now = Carbon::now()->format('Y-m-d');
        $apps_waitings = Waiting::join('patients', 'waitings.patient_id', '=', 'patients.id')
        ->join('appointments', 'patients.id', '=', 'appointments.patient_id')
        ->join('people', 'people.id', '=', 'patients.person_id')
        ->join('names', 'names.id', '=', 'people.name_id')
        ->leftJoin('phones', 'phones.person_id', 'people.id')
        ->orderBy('appointments.appointment_date', 'ASC')
        ->orderBy('appointments.appointment_time', 'ASC')
        ->where([['waitings.priority', '=', 0],['waitings.finished','=',0], ['waitings.doctor_id', '=', $doctor_id]])
        ->select(
            'waitings.id as waiting_id',
            'patients.person_id as patient_id',
            DB::raw("CONCAT(names.first_name,' ',names.last_name) AS patient_name"),
            DB::raw('TIMESTAMPDIFF(Year,people.birth_date,CURDATE()) as age'),
            'phones.phone_number',
        )
            ->get();
            // return $apps_waitings;
        $emergencies = Waiting::join('patients', 'waitings.patient_id', '=', 'patients.id')
        ->join('people', 'people.id', '=', 'patients.person_id')
        ->join('names', 'names.id', '=', 'people.name_id')
        ->leftJoin('phones', 'phones.person_id', 'people.id')
        ->orderBy('waitings.created_at', 'ASC')
        ->where([['waitings.priority', '=', 1],['waitings.finished','=',0], ['waitings.doctor_id', '=', $doctor_id]])
        ->select(
            'waitings.id as waiting_id',
            'patients.person_id as patient_id',
            DB::raw("CONCAT(names.first_name,' ',names.last_name) AS patient_name"),
            DB::raw('TIMESTAMPDIFF(Year,people.birth_date,CURDATE()) as Age'),
            'phones.phone_number',
        )
        ->get();
        $waitings = $emergencies->concat($apps_waitings);

        //check if empty
        if($waitings->isEmpty())return $this->okResponse(null,__('msg.there_are_no_patient_in_waiting'));

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
           $waitings = $this->paginate($waitings->toArray());
       }

        return $this->okResponse($waitings);
    }

    public function getLastWaiting()//Doctor
    {
        $doctor_id=auth()->user()->person->id;
        $req=new Request();
        $req->replace(['doctor_id'=>$doctor_id]);
        $h=$this->getDoctorWaitings($req);
        $h=$h->getContent();
        $h=json_decode($h,true);
        if(!$h['data'])
            return $h;
        return $this->okResponse($h['data'][0]);
    }


}
