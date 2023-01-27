<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;

class PaymentController extends Controller
{
    use ApiResponderTrait;
    public function add(Request $request)
    {
        $data=$request->only('employee_id','value','payment_type','description');
        $employee_id=\App\Models\Person::find($request->employee_id)->employee->id??'false';
        $data['employee_id']=$employee_id;
        $rules=[
            'employee_id'=>'required|not_in:false',
            'value'=>'required|gt:0',
            'description'=>'string'
        ];
        $validator=\Illuminate\Support\Facades\Validator::make($data,$rules);
        if($validator->fails())
            return $this->badRequestResponse($validator->errors()->all());
        \App\Models\BalancePayment::add($data['employy_id'],$data['value'],$data['payment_type'],$data['description']);
        return $this->okResponse(null);
    }
    public function getBalance()
    {
        $user=auth()->user();
        $employee_id=auth()->user()->person->employee->id;
        $payments=\App\Models\BalancePayment::query()
        ->where('employee_id',$employee_id)
        ->where('is_added',0)
        ->get();
        $count=0;
        $details=[];
        foreach($payments as $payment)
        {
            if(in_array($payment->payement_type->id,[1,3]))
            {
                $count+=$payment->balance;
                $details[]=[
                    'value'=>$payment->balance,
                    'balance type'=>$payment->payement_type->name,
                    'reason'=>$payment->description
                ];
            }
            else if($payment->payement_type->id==2)
            {
                $count-=$payment->balance;
                $details[]=[
                    'value'=>$payment->balance,
                    'balance type'=>$payment->payement_type->name,
                    'reason'=>$payment->description
                ];
            }
        }
        $data=[
            'details'=>$details,
            'total'=>$count
        ];
        if($user->hasRole('Doctor'))
        {
            $doctor_rate=\App\Models\BalancePayment::getDoctorRate(auth()->user()->person->employee->doctor->id);
            $data['rate']=$doctor_rate;
        }
        return $this->okResponse($data);
    }

}
