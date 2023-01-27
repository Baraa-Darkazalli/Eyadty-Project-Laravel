<?php

namespace App\Jobs;

use App\Models\Name;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddSalary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $employee_id;
    public function __construct($employee_id)
    {
        $this->employee_id=$employee_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $employee=\App\Models\Employee::find($this->employee_id);
        if(isset($employee->doctor))
        {
            $doctor_rate=\App\Models\BalancePayment::getDoctorRate($employee->doctor->id);
            \App\Models\BalancePayment::add($this->employee_id,$doctor_rate,'Rate');
        }
        $employee_salary=$employee->salary;
        \App\Models\BalancePayment::add($this->employee_id,$employee_salary,'Monthly salary');
    }
}
