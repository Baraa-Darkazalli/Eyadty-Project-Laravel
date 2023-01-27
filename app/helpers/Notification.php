<?php
namespace App\helpers;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\BaseNotification;
use Illuminate\Support\Facades\Http;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class Notification{
    use ApiResponderTrait;
    public static function sendFcm($user,$title,$body)
    {
        $server_key=env('FIRE_BASE_API');
        $fcm_token=$user->fcm_token??false;
        if(!$fcm_token)
            return response('this user have not fcm token',204);
        $fcm=Http::acceptJson()
        ->withToken($server_key)
        ->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'to'=>$fcm_token,
                'notification'=>
                [
                    'title'=>$title,
                    'body'=>$body
                ]
            ]
                );
                return $fcm;

    }
    public static function sendNotification($user,$data)
    {
        FacadesNotification::send($user,new BaseNotification($data));
    }
    public static function sendWithFcm($user,$title,$body,$data)
    {
        Notification::sendFcm($user,$title,$body);
        Notification::sendNotification($user,$data);
    }
    public static function delayAppointment($appointment_id)
    {
        $appointment=Appointment::find($appointment_id);
        $user=$appointment->patient->person->user;
        $doctor_session_duration=$appointment
        ->doctor
        ->session_duration
        ->session_duration;
        $old_time=$appointment->appointment_time;
        $new_time=Carbon::parse($old_time)
        ->addMinutes($doctor_session_duration)
        ->format('H:i');
        $title='sorry';
        $body='Sorry we delayed your appointment';
        $data=[
            'old appointment time'=>$old_time,
            'new appointment time'=>$new_time,
            'new appointment_day'=>$appointment->appointment_date,
            'title'=>$title,
            'body'=>$body
        ];
        Notification::sendWithFcm($user,$title,$body,$data);
    }

}
