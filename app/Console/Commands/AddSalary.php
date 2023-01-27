<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddSalary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddSalary';

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
        $employees=\App\Models\Employee::all();
        foreach($employees as $employee)
        {
            \App\Jobs\AddSalary::dispatch($employee->id);
        }
    }
}
