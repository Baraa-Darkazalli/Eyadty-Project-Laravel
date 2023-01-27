<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAppointment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    // protected $appointment;
    protected $appointment;

    public function __construct($appointment)
    {
        $this->appointment=$appointment;
        // $this->appointment=$appointment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $admin_id=\App\Models\User::where('username','admin')->first()->id;
        $now=Carbon::parse(now());
        $appointment_time=Carbon::parse($this->appointment->appointment_time);
        if($now->gte($appointment_time->addMinutes(10)))
        {
            if(isset($this->appointment->waiting->patient->person->user))
            {
                $reciver_id=$this->appointment->waiting->patient->person->user->id;
                $title='About your appointment';
                $content="sorry we delayed your appointment:{$this->appointment->appointment_time},{$this->appointment->appointment_date}";
                $data=[
                    'sender_id'=>$admin_id,
                    'receiver_id'=>$reciver_id,
                    'title'=>$title,
                    'content'=>$content
                ];

                \App\Models\Notification::sendNotification($data);
            }
            $this->appointment->update(['appointment_statue_id'=>4]);
        }
    }
}
