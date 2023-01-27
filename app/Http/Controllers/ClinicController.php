<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\ClinicName;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class ClinicController extends Controller
{
    use ApiResponderTrait;
    public function getClinics()//role:reception
    {
        $clinics=Clinic::join('clinic_names','clinics.clinic_name_id','clinic_names.id')
        ->select('clinics.id','clinic_names.name')
        ->get();
        return $this->okResponse($clinics);
    }

    public function addName(Request $request)//role:Admin
    {
        $data = $request->only('department_id', 'name');
        $rules = [
            'department_id' => 'required',
            'name' => 'required|unique:clinic_names',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $clinic_name = new \App\Models\ClinicName();
            $clinic_name->name = $request->name;
            $clinic_name->department_id = $request->department_id;
            $clinic_name->save();

            $data=[
                'id'=>$clinic_name->id
            ];

            return $this->createdResponse($data, __('msg.clinic_name_added_successfully'));
        }
    }

    public function add(Request $request)//role:Admin
    {
        $data = $request->only('clinic_name_id', 'session_price');
        $rules = [
            'clinic_name_id' => 'required|unique:clinics,clinic_name_id|exists:clinic_names,id',
            'session_price' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $clinic = new \App\Models\Clinic();
            $clinic->clinic_name_id = $request->clinic_name_id;
            $clinic->session_price = $request->session_price;
            $clinic->save();
            return $this->createdResponse(null,__('msg.clinic_created_successfully'));
        }
    }

    public function getCLinicsByDepartmentId(Request $request)//role:Admin|Patient
    {
        if(!Department::find($request->id))return $this->notFoundResponse();
        $clinics=Clinic::all();
        foreach ($clinics as $clinic) {
            if($clinic->clinic_name->clinic_department->id!=$request->id)continue;
            $data[]=[
                'id'=>$clinic->id,
                'name'=>$clinic->clinic_name->name,
                'session_price'=>$clinic->session_price,
                'doctors_count'=>Doctor::where('clinic_id','=',$clinic->id)->count()
            ];
        }
        $data=$data??false;
        if(!$data)return $this->okResponse(null,__('msg.there_are_no_clinics_in_this_department'));

        return $this->okResponse($data);
    }

    public function getClinicsNames()//role:Admin
    {
        $clinics_names=ClinicName::all();
        if(empty($clinics_names->count()))return $this->okResponse(null,__('msg.there_are_no_clinics_names'));
        foreach ($clinics_names as $clinic_name) {
            if(Clinic::where('clinic_name_id',$clinic_name->id)->exists())continue;
            $data[]=[
                'id'=>$clinic_name->id,
                'clinic_name'=>$clinic_name->name,
            ];
        }
        if(empty($data))return $this->okResponse(null,__('msg.there_are_no_clinics_names'));
        return $this->okResponse($data);
    }

    public function getMyClinic()//Doctor
    {
        $doctor=User::find(auth()->user()->id)->person->employee->doctor??false;
        if(!$doctor)return $this->badRequestResponse();

        foreach ($doctor->clinic->doctors as $clinic_doctor) {
            if($clinic_doctor->id==$doctor->id)continue;
            $doctors[]=[
                'id' => $clinic_doctor->employee->person->id,
                'name' => $clinic_doctor->employee->person->name->first_name.' '.$clinic_doctor->employee->person->name->last_name,
                'clinic_name'=>$clinic_doctor->clinic->clinic_name->name,
                'previous_experience' => $doctor->employee->previous_experience+Carbon::parse($doctor->created_at)->diff(Carbon::now())->y,
            ];
        }
        foreach ($doctor->clinic->nurses as $nurse) {
            $nurses[]=[
                'id' => $nurse->employee->person->id,
                'name' => $nurse->employee->person->name->first_name.' '.$nurse->employee->person->name->last_name,
                'clinic_name'=>$clinic_doctor->clinic->clinic_name->name,
                'previous_experience' => $nurse->employee->previous_experience+Carbon::parse($doctor->created_at)->diff(Carbon::now())->y,
            ];
        }

        $data=[
            'session_price'=>$doctor->clinic->session_price,
            'doctors'=>$doctors??null,
            'nurses'=>$nurses??null
        ];
        return $this->okResponse($data);
    }

    public function search(Request $request)//role:Admin|Patient
    {
        if(!Department::find($request->department_id))return $this->notFoundResponse();
        $clinics=Clinic::join('clinic_names','clinic_names.id','clinics.clinic_name_id')
                            ->where('clinic_names.name','LIKE','%'.$request->input.'%')->get();
        foreach ($clinics as $clinic) {
            if($clinic->clinic_name->clinic_department->id!=$request->department_id)continue;
            $data[]=[
                'id'=>$clinic->id,
                'name'=>$clinic->clinic_name->name,
                'session_price'=>$clinic->session_price,
                'doctors_count'=>Doctor::where('clinic_id','=',$clinic->id)->count()
            ];
        }
        $data=$data??false;
        if(!$data)return $this->okResponse(null,__('msg.input_not_found'));

        return $this->okResponse($data);
    }
}
