<?php

namespace App\Models;

use App\Traits\ApiResponderTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory,ApiResponderTrait;

    public static function sendFcm($user_id,$title,$body)
    {
        $server_key=env('AAAAn1UCti0:APA91bHlAN2LSSERMzwCd4lwsZ5YR8usxXADMoKFo_jCdC8-jWCv6OfK48tkfYLfD_Kdp1JBDDGrIVG7UHpo1y_usUetUEIUSN-Tq5bAf6J2fpuDdYZbSejlGPu2QMZzmN5eaGn2dTwF');
        if(\Illuminate\Support\Facades\DB::table('users')->where('id','=',$user_id)->whereNotNull('fcm_token')->exists())
        {
        $fcm_token=\App\Models\User::find($user_id)->fcm_token;
        $fcm=\Illuminate\Support\Facades\Http::acceptJson()->withToken($server_key)->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'notification'=>[
                    'title'=>$title,
                    'body'=>$body,
                ]
            ]
            );
            return $fcm;
        }
        return false;
    }
    public static function sendNotification($inputs)
    {
        if(isset($inputs['sender_id']))
        {
            $sender_id=$inputs['sender_id'];
        }
        else
        {
            $sender_id = auth()->user()->id;
        }
        $receiver_id = $inputs['receiver_id'];

        $notification = new Notification();
        $notification->title = $inputs['title'];
        $notification->body = $inputs['content'];
        $notification->user_id = $sender_id;
        $notification->save();
        $notification->users()->attach($receiver_id);

        return true;
    }

    public function users()
    {
        //المستلم
        return $this->belongsToMany(User::class, 'notification_users', 'notification_id', 'user_id')
        ->whereNull('notification_users.deleted_at')
        ->withTimestamps()
        ->withPivot(['deleted_at']);
    }

    public function senders()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); //المرسل
    }
}
