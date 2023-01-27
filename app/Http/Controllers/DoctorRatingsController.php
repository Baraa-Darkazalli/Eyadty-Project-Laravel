<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorRating;
use App\Models\Person;
use App\Models\User;
use App\Models\Waiting;
use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use Illuminate\Support\Facades\Validator;

class DoctorRatingsController extends Controller
{
    use ApiResponderTrait;

    public function setRating(Request $request)//role:Patient
    {
        $data=$request->only('doctor_id','rating_id');
        $rules=[
            'doctor_id'=>'required|exists:people,id',
            'rating_id'=>'required|exists:rating_doctor_values,id'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails())
            return $this->badRequestResponse($validator->errors()->all());

        //set rating
        $user_id=auth()->user()->id;
        $user=User::find(auth()->user()->id);
        $doctor=Person::find($request->doctor_id)->employee->doctor??false;
        if(!$doctor)return $this->badRequestResponse(__('msg.this_is_failed_id'));

        $doctor_rating=DoctorRating::where([['doctor_id',$doctor->id],['user_id',$user_id]])->first();
        if(empty($doctor_rating))
            $doctor_rating=new DoctorRating();
        $doctor_rating->rating_doctor_value_id=$request->rating_id;
        $doctor_rating->user_id=$user_id;
        $doctor_rating->doctor_id=$doctor->id;
        $doctor_rating->save();

        return $this->okResponse(null,__('msg.rating_set_successfully'));
    }
}
