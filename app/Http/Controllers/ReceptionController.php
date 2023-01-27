<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Reception;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Empty_;

class ReceptionController extends Controller
{
    use ApiResponderTrait;
    public function add(Request $request)//role:Admin
    {
        $user_data=$request->only(
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

        //create reception
        $reception = new Reception();
        $reception->employee_id = $result['employee_id'];
        $reception->save();

        //create user
        $rules =[
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
            $user->email = $user_data['email']??null;
            $user->save();
            $user->attachRole('Doctor');
        }

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
        return $this->okResponse(null,__('msg.reception_created_successfully'));
    }
}
