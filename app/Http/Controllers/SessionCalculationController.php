<?php

namespace App\Http\Controllers;

use App\Models\SessionCalculation;
use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use App\Traits\HelperTrait;

class SessionCalculationController extends Controller
{
    use ApiResponderTrait,HelperTrait;

    public function getAllSessionsCalculations($type=false)//role:Reception
    {
        if(empty(SessionCalculation::where('is_paid','=',false)->count()))
            return $this->okResponse(null,__('msg.there_are_no_sessions_not_paided'));
    
        $session_calcs=SessionCalculation::where('is_paid','=',false);
        foreach ($session_calcs as $session_calc) {
            $data[]=[
                'calculation_id'=>$session_calc->id,
                'patient_id'=>$session_calc->session->waiting->patient->person->id,
                'patient_name'=>$session_calc->session->waiting->patient->person->name->first_name.' '.$session_calc->session->waiting->patient->person->name->last_name,
                'session_date'=>$session_calc->session->session_date,
                'session_time'=>$session_calc->session->session_time,
                'doctor_name'=>$session_calc->session->waiting->doctor->employee->person->name->first_name.''.$session_calc->session->waiting->doctor->employee->person->name->first_name,
                'is_review'=>$session_calc->session->is_review,
                'payment_statues'=>$session_calc->is_paid,
            ];
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function makePaided(Request $request)//role:Reception
    {
        $session_calc=SessionCalculation::find($request->calculation_id)??false;
        if(!$session_calc)return $this->badRequestResponse(__('msg.this_is_failed_id'));

        $session_calc->is_paid=true;
        return $this->okResponse(null,__('msg.session_paided_successfully'));
    }
}
