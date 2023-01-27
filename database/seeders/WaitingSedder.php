<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Waiting;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PhpParser\Node\Stmt\Continue_;

class WaitingSedder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createWaiting(10);
    }
    public function createWaiting($count=10)
    {
        while ($count>0) {
            $waiting=new Waiting();
            $waiting->patient_id=Patient::first()->id;
            $waiting->doctor_id=Doctor::first()->id;
            $waiting->priority=1;
            $waiting->save();
            $count--;
        }
    }
}
