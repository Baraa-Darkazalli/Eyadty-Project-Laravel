<?php

namespace App\Listeners;

use App\Events\AddEmergencyPatient;
use App\Models\Appointment;
use App\Models\AppointmentStatue;
use App\Models\Doctor;
use Carbon\Carbon;

class ShiftingAppointmentsTimes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AddEmergencyPatient  $event
     * @return void
     */
    public function handle(AddEmergencyPatient $event)
    {
        $admin_id=\App\Models\User::where('username','admin')->first()->id;
        $pendeng_statue_id = AppointmentStatue::where('name', 'Pending')->first()->id;
        $appointments = Appointment::where([['doctor_id', $event->doctor_id], ['appointment_date', $event->date], ['appointment_statue_id', $pendeng_statue_id]])
        ->orderBy('appointment_time','ASC')
        ->get();
        foreach ($appointments as $appointment) {
            $appointment_time = $appointment->appointment_time;
            $appointment_time = Carbon::parse($appointment_time);
            $session_duration = Carbon::parse(Doctor::find($event->doctor_id)->session_duration->session_duration)->minute;
            $appointment_time->addMinutes($session_duration);
            $appointment_time=$appointment_time->format('H:i');
            $appointment->appointment_time = $appointment_time;
            if(Appointment::checkIfAvailable($event->date,$appointment_time,$event->doctor_id)){
                $appointment->save();
                if(isset($appointment->patient->person->user->id))
                {
                    $data=[
                        'sender_id'=>$admin_id,
                        'receiver_id'=>$appointment->patient->person->user->id,
                        'title'=>'shift your appointment',
                        'content'=>'sorry we shift your appointment'
                    ];
                    \App\Models\Notification::sendNotification($data);
                }
                break;
            }
            if(isset($appointment->patient->person->user->id))
            {
                $data=[
                    'sender_id'=>$admin_id,
                    'receiver_id'=>$appointment->patient->person->user->id,
                    'title'=>'shift your appointment',
                    'content'=>'sorry we shift your appointment'
                ];
                \App\Models\Notification::sendNotification($data);
            }
            $appointment->save();
        }
    }
}
