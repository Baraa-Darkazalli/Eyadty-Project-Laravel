<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\BalancePayment;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\Session;
use App\Models\SessionCalculation;
use App\Models\Waiting;
use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    use ApiResponderTrait;

    public function getbookingSourceRate()
    {
        $all_appointment_count=Appointment::count();
        $patients_appointment_count=Appointment::whereNull('reception_id')->count();
        $receptions_appointment_count=Appointment::whereNotNull('reception_id')->count();
        if(empty($all_appointment_count))
        $data=[
            'reception_rate'=>0,
            'patient_rate'=>0
        ];
        else $data=[
            'reception_rate'=>round($receptions_appointment_count*100/$all_appointment_count),
            'patient_rate'=>round($patients_appointment_count*100/$all_appointment_count)
        ];
        return $this->okResponse($data);
    }

    public function getAppointmentStatuesRate()
    {
        //All appointment count + emergency sessoins count
        $total_count=Appointment::count()+Waiting::where([['priority','=',1],['finished','=',1]])->count();

        $pending=Appointment::where('appointment_statue_id','=',1)->count();
        $canceled=Appointment::where('appointment_statue_id','=',2)->orWhere('appointment_statue_id','=',4)->count();
        $done=Session::count();
        if(empty($total_count))
        $data=[
            'pending_rate'=>0,
            'canceled_rate'=>0,
            'done_rate'=>0
        ];
        else $data=[
            'pending_rate'=>round($pending*100/$total_count),
            'canceled_rate'=>round($canceled*100/$total_count),
            'done_rate'=>round($done*100/$total_count)
        ];
        return $this->okResponse($data);
    }

    public function getSimpleInfo()
    {

        //patient,sessinos,employees ---> count
        $patients=Patient::count();
        $employees=Employee::count();
        $sessions=Session::count();

        //total incoming
        $incoming=0;
        if(!empty(SessionCalculation::where('is_paid','=',true)->count())){
            $session_calcs=SessionCalculation::where('is_paid','=',true)->get();
            foreach ($session_calcs as $sessoin_calc) {
                $incoming+=$sessoin_calc->session->waiting->doctor->clinic->session_price;
                $extra_treatments=$sessoin_calc->extra_treatments??false;
                if($extra_treatments){
                    foreach ($extra_treatments as $extra_treatmente) {
                        $incoming+=$extra_treatmente->treatment_price;
                    }
                }
            }
        }

        //total payments
        $payments=0;
        if(!empty(BalancePayment::where('is_added','=',true)->count()))
            $payments+=BalancePayment::where('is_added','=',true)->get(DB::raw('SUM(balance)'));
        if(!empty(SessionCalculation::where('is_paid','=',false)->count())){
            $session_calcs=SessionCalculation::where('is_paid','=',false)->get();
            foreach ($session_calcs as $sessoin_calc) {
                $extra_treatments=$sessoin_calc->extra_treatments??false;
                if($extra_treatments){
                    foreach ($extra_treatments as $extra_treatmente) {
                        $payments+=$extra_treatmente->item_price??0;
                    }
                }
            }
        }

        //total profits
        $profits=$incoming-$payments;


        $data=[
            'patients_count'=>$patients,
            'employees_count'=>$employees,
            'sessions_count'=>$sessions,
            'total_incoming'=>$incoming,
            'total_payments'=>$payments,
            'total_profits'=>$profits,
        ];

        return $this->okResponse($data);
    }


    public function getSessionsTypeCount()
    {
        //yearly->review,new,->12 valuse without key

        $FirstDayOfThisYear= date('Y-m-d', strtotime('first day of january this year'));

        for ($month=1; $month <=12 ; $month++) {
            $review_sessions=Session::where('is_review','=',true);
            $new_sessions=Session::where('is_review','=',false);
            $start = Carbon::createFromFormat('Y-m-d',$FirstDayOfThisYear)->addMonths($month-1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::createFromFormat('Y-m-d',$FirstDayOfThisYear)->addMonths($month-1)->endOfMonth()->format('Y-m-d');

            $review_year[]=$review_sessions->whereBetween('session_date', [$start, $end])->count();
            $new_year[]=$new_sessions->whereBetween('session_date',[$start,$end])->count();

        }
        $yearly=[
            'review'=>$review_year,
            'new'=>$new_year
        ];
        //monthly//////////,->31,30,29,28 ///////
        $FirsttDayOfThisMonth=date('d', strtotime('first day of this month'));
        $LastDayOfThisMonth=date('d', strtotime('last day of this month'));
        for ($day=1; $day <=(int)$LastDayOfThisMonth ; $day++) {
                $review_sessions=Session::where('is_review','=',true);
                $new_sessions=Session::where('is_review','=',false);
                $this_day=Carbon::createFromFormat('d',$FirsttDayOfThisMonth)->addDays($day-1)->format('d');
                $review_month[]=$review_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count();
                $new_month[]=$new_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count();

            }
        $monthly=[
            'review'=>$review_month,
            'new'=>$new_month
        ];
        //daily/////////,24////
        for ($hour=0; $hour <=23 ; $hour++) {
            $review_sessions=Session::where('is_review','=',true);
            $new_sessions=Session::where('is_review','=',false);
                $this_hour=Carbon::createFromFormat('H',"0")->addHours($hour)->format('H');
                $review_day[]=$review_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count();

                $new_day[]=$new_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count();


            }
        $daily=[
            'review'=>$review_day,
            'new'=>$new_day
        ];
        $data=[
            'yearly'=>$yearly,
            'monthly'=>$monthly,
            'daily'=>$daily,
        ];
        return $this->okResponse($data);
    }
    
    // public function getProfitsDetails()
    // {
    //     //yearly->review,new,->12 valuse without key
        
    //     $FirstDayOfThisYear= date('Y-m-d', strtotime('first day of january this year'));
        
    //     for ($month=1; $month <=12 ; $month++) {
    //         //total incoming
    //         $incoming=0;
    //         $session_calcs=SessionCalculation::join('sessions','sessions.id','session_calculations.id')

    //         if(!empty(SessionCalculation::join('sessions','sessions.id','session_calculations.id')
    //         ->where([['is_paid','=',true],['session_date',Carbon::parse()->format('Y-m-d')]])->count()))
            
    //     {
    //         ->where([['is_paid','=',true],['session_date',Carbon::parse()->format('Y-m-d')]])->get();
    //         foreach ($session_calcs as $sessoin_calc) {
    //             $incoming+=$sessoin_calc->session->waiting->doctor->clinic->session_price;
    //             $extra_treatments=$sessoin_calc->extra_treatments??false;
    //             if($extra_treatments){
    //                 foreach ($extra_treatments as $extra_treatmente) {
    //                     $incoming+=$extra_treatmente->treatment_price;
    //                 }
    //             }
    //         }
    //     }
        
    //     //total payments
    //     $payments=0;
    //     if(!empty(BalancePayment::where('is_added','=',true)->whereDate('created_at',Carbon::today()->toDateString())->count()))
    //         $payments+=BalancePayment::where('is_added','=',true)->whereDate('created_at',Carbon::today()->toDateString())->get(DB::raw('SUM(balance)'));
    //     if(!empty(SessionCalculation::where('is_paid','=',false)->count())){
    //         $session_calcs=SessionCalculation::join('sessions','sessions.id','session_calculations.id')
    //         ->where([['is_paid','=',true],['session_date',Carbon::parse()->format('Y-m-d')]])->get();
    //         foreach ($session_calcs as $sessoin_calc) {
    //             $extra_treatments=$sessoin_calc->extra_treatments??false;
    //             if($extra_treatments){
    //                 foreach ($extra_treatments as $extra_treatmente) {
    //                     $payments+=$extra_treatmente->item_price??0;
    //                 }
    //             }
    //         }
    //     }
        
    //     //total profits
    //     $profits=$incoming-$payments;
    //         $start = Carbon::createFromFormat('Y-m-d',$FirstDayOfThisYear)->addMonths($month-1)->startOfMonth()->format('Y-m-d');
    //         $end = Carbon::createFromFormat('Y-m-d',$FirstDayOfThisYear)->addMonths($month-1)->endOfMonth()->format('Y-m-d');
            
    //         $review_year[]=$review_sessions->whereBetween('session_date', [$start, $end])->count();
    //         // $review_year[]=[
    //         //     "{$month}"=>$review_sessions->whereBetween('session_date', [$start, $end])->count(),
    //         // ];
    //         $new_year[]=$new_sessions->whereBetween('session_date',[$start,$end])->count();
    //         // $new_year[]=[
    //         //     "{$month}"=>$new_sessions->whereBetween('session_date',[$start,$end])->count(),
    //         // ];
            
    //     }
    //     $yearly=[
    //         'review'=>$review_year,
    //         'new'=>$new_year
    //     ];
    //     //monthly//////////,->31,30,29,28 ///////
    //     $FirsttDayOfThisMonth=date('d', strtotime('first day of this month'));
    //     $LastDayOfThisMonth=date('d', strtotime('last day of this month'));
    //     for ($day=1; $day <=(int)$LastDayOfThisMonth ; $day++) {
    //             $review_sessions=Session::where('is_review','=',true);
    //             $new_sessions=Session::where('is_review','=',false);
    //             $this_day=Carbon::createFromFormat('d',$FirsttDayOfThisMonth)->addDays($day-1)->format('d');
    //             $review_month[]=$review_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count();
    //             // $review_month[]=[
    //             //     "{$day}"=>$review_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count(),
    //             // ];
    //             $new_month[]=$new_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count();
    //             // $new_month[]=[
    //             //     "{$day}"=>$new_sessions->where(DB::raw('day(session_date)'),'=',$this_day)->count(),
    //             // ];
                
    //         }
    //     $monthly=[
    //         'review'=>$review_month,
    //         'new'=>$new_month
    //     ];
    //     //daily/////////,24////
    //     for ($hour=0; $hour <=23 ; $hour++) {
    //         $review_sessions=Session::where('is_review','=',true);
    //         $new_sessions=Session::where('is_review','=',false);
    //             $this_hour=Carbon::createFromFormat('H',"0")->addHours($hour)->format('H');
    //             $review_day[]=$review_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count();
    //             // $review_day[]=[
    //             //     "{$hour}"=>$review_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count(),
    //             // ];
    //             $new_day[]=$new_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count();
    //             // $new_day[]=[
    //             //     "{$hour}"=>$new_sessions->where(DB::raw('hour(session_time)'),'=',$this_hour)->count(),
    //             // ];
                
    //         }
    //     $daily=[
    //         'review'=>$review_day,
    //         'new'=>$new_day
    //     ];
    //     $data=[
    //         'yearly'=>$yearly,
    //         'monthly'=>$monthly,
    //         'daily'=>$daily,
    //     ];
    //     return $this->okResponse($data);
    // }
}
