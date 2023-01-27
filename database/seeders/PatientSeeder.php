<?php

namespace Database\Seeders;

use App\Http\Controllers\AppointmentController;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\ExtraTreatments;
use App\Models\Patient;
use App\Models\SessionCalculation;
use App\Models\User;
use App\Models\Waiting;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->craete(100);
        // $this->assignAccounts();
        $this->appointments(25);
        $this->waitings();
        $this->sessions();
    }
    public function craete($count=1)
    {
        Patient::factory($count)->create();
    }
    public function assignAccounts()
    {
        $faker=Factory::create();
        $patients=Patient::all();
        $patients=$faker->randomElements($patients,$patients->count()/4);
        foreach($patients as $patient)
        {
            $user=User::factory()->create(['person_id'=>$patient->person->id]);
            $user->attachRole('Patient');

        }
    }
    public function appointments($count=1)
    {
        $a=$count;
        $doctors_ids=Doctor::query()->pluck('id');
        $faker=Factory::create();
        foreach($doctors_ids as $doctor_id)
        {
            $person_id=Doctor::find($doctor_id)->employee->person->id;
            while($count!=0)
            {
                $date=$faker->dateTimeBetween('+1 days','+31 days')->format('Y-m-d');
                $req=new Request();
                $req->replace(['date'=>$date,'doctor_id'=>$person_id]);
                $available_times=(new AppointmentController)->getAvailableApps($req);
                if($available_times->status()==200)
                {
                    $available_times=$available_times->getOriginalContent()['data'];
                }
                else
                {
                    $count--;
                    continue;
                }
                $patients_ids=Patient::query()->pluck('id');
                $patient_id=$faker->randomElement($patients_ids);
                $time=$faker->randomElement($available_times);
                if(Appointment::where('appointment_statue_id',2)->where('patient_id',$patient_id)->count()>=3)
                {
                    $appointment_statue_id=1;
                }
                else
                {
                    $appointment_statue_id=$faker->randomElement([1,2]);
                }
                $count--;
                Appointment::factory()->create(['doctor_id'=>$doctor_id,'patient_id'=>$patient_id,'appointment_statue_id'=>$appointment_statue_id,'appointment_time'=>$time,'appointment_date'=>$date,'is_review'=>$faker->randomElement([1,0])]);
            }
        }
        $count=$a;
        foreach($doctors_ids as $doctor_id)
        {
            $person_id=Doctor::find($doctor_id)->employee->person->id;
            while($count!=0)
            {
                $date=$faker->dateTimeBetween('-100 days','-1 days')->format('Y-m-d');
                $req=new Request();
                $req->replace(['date'=>$date,'doctor_id'=>$person_id]);
                $available_times=(new AppointmentController)->getAvailableApps($req);
                if($available_times->status()==200)
                {
                    $available_times=$available_times->getOriginalContent()['data'];
                }
                else
                {
                    $count--;
                    continue;
                }
                $patients_ids=Patient::query()->pluck('id');
                $patient_id=$faker->randomElement($patients_ids);
                $time=$faker->randomElement($available_times);
                $appointment_statue_id=$faker->randomElement([2,3,4]);
                $count--;
                Appointment::factory()->create(['doctor_id'=>$doctor_id,'patient_id'=>$patient_id,'appointment_statue_id'=>$appointment_statue_id,'appointment_time'=>$time,'appointment_date'=>$date,'is_review'=>$faker->randomElement([1,0])]);
            }
        }
    }
    public function waitings()
    {
        $today=Carbon::parse()->format('Y-m-d');
        $appointments=\App\Models\Appointment::query()
        ->whereDate('appointment_date','<',$today)
        ->where('appointment_statue_id',3)
        ->get();
        foreach($appointments as $appointment)
        {
            $waiting=new \App\Models\Waiting();
            $waiting->appointment_id=$appointment->id;
            $waiting->doctor_id=$appointment->doctor_id;
            $waiting->patient_id=$appointment->patient_id;
            $waiting->priority=0;
            $waiting->finished=1;
            $waiting->save();
        }
    }
    public function sessions()
    {
        // Waiting::query()->delete();
        $faker=Factory::create();
        $waitings=\App\Models\Waiting::query()->where('finished',1)->whereNotNull('appointment_id')->get();
        foreach($waitings as $waiting)
        {
            \App\Models\Session::factory()->has(SessionCalculation::factory()->has(ExtraTreatments::factory()->count($faker->randomElement([1,2,3,4,5])))->count(1))
            ->create(
                [
                    'waiting_id'=>$waiting->id,
                    'session_date'=>$waiting->appointment->appointment_date,
                    'session_time'=>$waiting->appointment->appointment_time
                ]
            );
        }
    }
}
