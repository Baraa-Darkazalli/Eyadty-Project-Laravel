<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalancePayment extends Model
{
    use HasFactory;

    public static function add($employee_id,$value,$payment_type,$description='')
    {
        $payment_type_id=\App\Models\PaymentType::query()->where('name',"{$payment_type}")->first()->id;
        // $employee_id=\App\Models\Person::find($employee_id)->employee->id??'false';
        // $data=[
        //     'employee_id'=>$employee_id,
        //     'value'=>$value,
        //     'description'=>$description
        // ];
        // $rules=
        // [
        //     'employee_id'=>'required|not_in:false',
        //     'value'=>'required|gt:0',
        //     'description'=>'string'
        // ];
        // $validator=\Illuminate\Support\Facades\Validator::make($data,$rules);
        // if($validator->fails())
        //     return [
        //         'success'=>0,
        //         'errors'=>$validator->errors()
        //     ];
        $payment=new \App\Models\BalancePayment();
        $payment->employee_id=$employee_id;
        $payment->payment_type_id=$payment_type_id;
        $payment->balance=$value;
        $payment->save();
        // $payment->description=$data['descrtipion']??null;
        // return $payment;
    }
    public static function getDoctorRate($doctor_id)
    {
        $doctor=\App\Models\Doctor::find($doctor_id);
        $current_year=\Carbon\Carbon::parse()->year;
        $last_month=\Carbon\Carbon::parse()->subMonth()->month;
        $waitings=\App\Models\Waiting::query()
        ->where('doctor_id',$doctor_id)
        ->where('finished',1)
        ->whereMonth('created_at',$last_month)
        ->whereYear('created_at',$current_year)
        ->get();
        $doctor_rate=$doctor->salary_rate;
        $session_price=$doctor->clinic->session_price;
        $rate_price=$session_price*$doctor_rate;
        $rate=0;
        foreach($waitings as $waiting)
        {
            $session=$waiting->session;
            $rate+=$rate_price;
            $session_calculation=$session->session_calculation;
            $extra_treatments=$session_calculation->extra_treatments;
            foreach($extra_treatments as $extra_treatment)
            {
                $rate+=$extra_treatment->treatment_price*$doctor_rate;
            }

        }
        return $rate;
    }
    public function payement_type()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
