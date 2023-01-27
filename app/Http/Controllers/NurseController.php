<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Employee;
use App\Models\Nurse;
use App\Models\Person;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;

class NurseController extends Controller
{
    use ApiResponderTrait;

    public function add(Request $request)//role:Admin
    {

        $employee_data = $request->only(
            'first_name',
            'last_name',
            'father_name',
            'gender',
            'birth_date',
            'previous_experience',
            'salary'
        );

        $nurse_data = $request->only(
            'clinic_id'
        );

        //create employee
        $result = \App\Models\Employee::createEmployee($employee_data);

        if ($result['success'] == 0)
            return $this->badRequestResponse($result['errors']);

        $user_data['person_id']=Employee::find($result['employee_id'])->person->id;

        //crate phones
        if(isset($request->phones)){
            $phones=$request->phones;
            if(empty($phones))return $this->badRequestResponse(__('msg.phones_are_empty'));
            foreach ($phones as $phone) {
                $person_id=Employee::find($result['employee_id'])->person->id;
                $phonesResult=PhoneController::addPhone($phone,$person_id);
                if ($phonesResult['success'] == 0)
                    return $this->badRequestResponse($phonesResult['message']);
            }
        }

        //create nurse
        $nurse = new Nurse();
        $nurse->employee_id = $result['employee_id'];
        $validator1=Validator::make([$request->clinic_id],['clinic_id'=>'required|exists:clinics,id']);
        if($validator1->fails())return $this->badRequestResponse($validator1->errors()->all());
        $clinic_id=$request->id??false;
        if(!$clinic_id)return $this->badRequestResponse(__('msg.invalid_clinic_id'));
        $nurse->clinic_id = $request->clinic_id;
        $nurse->save();

        //create working hours
        $assignResult=EmployeeController::assignWorkingTimes($request->working_times,$result['employee_id']);
        if($assignResult['success']==0)return $this->badRequestResponse($assignResult['message']);

        //create certificates
        if($request->has('certificates')&&(!empty($request->certificates))){
            foreach ($request->certificates as $certificate) {
                $data=array_merge($certificate,['employee_id'=>$result['employee_id']]);
                if(!CertificateController::addCertificate($data))
                    return $this->badRequestResponse(__('msg.invalid_value_in_certificates_data'));
            }
        }
        return $this->okResponse(null,__('msg.nurse_created_successfully'));
    }
    public function getSingleNurse(Request $request)//role: Admin|nurse
    {
        $nurse = (Person::find($request->nurse_id)->employee->nurse) ?? false;
        if (! $nurse) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        $data = [
            'nurse_name' => $nurse->employee->person->name->first_name.' '.$nurse->employee->person->name->last_name,
            'clinic_name' => $nurse->clinic->clinic_name->name,
            'phone_number' => ($nurse->employee->person->phones->first()->phone_number) ?? null,
            'previous_experience' => $nurse->employee->previous_experience+Carbon::parse($nurse->created_at)->diff(Carbon::now())->y,
            'age' => Carbon::parse($nurse->employee->person->birth_date)->diff(Carbon::now())->y,
            'gender' => $nurse->employee->person->gender,
        ];

        return $this->okResponse($data);
    }
}
