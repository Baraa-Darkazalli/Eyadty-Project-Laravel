<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Employee;
use App\Models\PreviousWork;
use App\Models\Reception;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public $employees_ids;
    public function run()
    {
        $this->employees_ids=Employee::query()->pluck('id');

        $this->createReception(10);
        // $this->assignDoctorAccounts();
        $this->assignPrevioesWork($this->employees_ids);
    }
    public function assignDoctorAccounts()
    {
        $doctors=Doctor::all();
        foreach($doctors as $doctor)
        {
            $user=User::factory()->create(['person_id'=>$doctor->employee->person->id]);
            $user->attachRole('Doctor');
        }
    }
    public function assignPrevioesWork($employees_ids)
    {
        $faker=Factory::create();
        $ran_number=$faker->numberBetween(1,3);
        foreach($employees_ids as $employee_id)
        {
            PreviousWork::factory($ran_number)->create(['employee_id'=>$employee_id]);
        }
    }
    public function createReception($count=1)
    {
        while($count!=0)
        {
            $reception=Reception::factory()->create();
            // $user=User::factory()->create(['person_id'=>$reception->employee->person->id]);
            // $user->attachRole('Reception');
            $count--;
        }
    }
}
