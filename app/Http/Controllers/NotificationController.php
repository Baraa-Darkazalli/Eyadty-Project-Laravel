<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use ApiResponderTrait;

    public function isThereNotification()//role: all
    {
        $user_id = auth()->user()->id;

        //return BOOLEAN vlaue is there notification not seen yet
        $isThere = NotificationUser::where([['user_id', '=', $user_id], ['is_seen', '=', false]])->first();
        if ($isThere) {
            return $this->okResponse(true);
        } else {
            return $this->okResponse(false);
        }
    }

    public function getAllNotifications()//role: all
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);

        //check if no notifications
        if (! DB::table('notification_users')->where([['user_id', $user_id], ['deleted_at', null]])->exists()) {
            return $this->okResponse(null, __('msg.no_notifications'));
        }

        //get all notifications
        foreach ($user->notifications as $notification) {
            $user->notifications()->updateExistingPivot($notification->id, ['is_seen' => true]);
            $full_name = ($notification->senders->person->name->first_name).' '.($notification->senders->person->name->last_name);
            $data[] = [
                'id' => $notification->id,
                'title' => $notification->title,
                'sender_name' => $full_name,
                'hours_ago' => $notification->created_at->diffInHours(Carbon::now()),
            ];
        }
        //sort data desc
        $data = array_reverse($data, false);
        // $data = collect ($data)->sortBy('updated_at')->reverse()->toArray();
        return $this->okResponse($data);
    }

    public function deleteNotification(Request $request)//role all
    {
        $user_id = auth()->user()->id;
        $notification_id = $request->id;

        //check if bad notification id
        if (! DB::table('notification_users')->where([['notification_id', $notification_id], ['user_id', $user_id], ['deleted_at', null]])->exists()) {
            return $this->badRequestResponse(__('msg.this_notice_does_not_exists'));
        }

        //soft delete for this notificationW
        DB::table('notification_users')
          ->where('user_id', $user_id)
          ->where('notification_id', $notification_id)
          ->update(['deleted_at' => DB::raw('NOW()')]);

        return $this->okResponse(null, __('msg.notice_deleted_successfully'));
    }

    public function getSingleNotification(Request $request)//role:all
    {
        $user_id = auth()->user()->id;
        $notification_id = $request->id;
        $notification = User::find($user_id)->notifications->find($notification_id);

        //check if no notifications
        if (! isset($notification)) {
            return $this->badRequestResponse(__('msg.this_id_is_not_for_notification'));
        }

        //get single notification
        $data = [
            'content' => $notification->body,
        ];

        return $this->okResponse($data);
    }
}
