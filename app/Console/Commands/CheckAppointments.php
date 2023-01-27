<?php

namespace App\Console\Commands;

use App\Jobs\CheckAppointment;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckAppointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $before_one_houre=Carbon::parse()->subHour()->minute(50)->format('H:i');
        $appointments=Appointment::whereDate('appointment_date',date('Y-m-d'))
        ->whereTime('appointment_time','>=',$before_one_houre)
        ->whereTime('appointment_time','<',date('H:i'))
        ->where('appointment_statue_id',1)
        ->get();
        $appointments->each(function($appointment){
            CheckAppointment::dispatch($appointment);
        });
    }
}
