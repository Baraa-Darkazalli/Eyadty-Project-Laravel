<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Day;
use App\Models\WorkingHour;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkingHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createTimes();
        $this->assignTimesDoctor();
    }

    public function createTimes()
    {
        $times =
        [
            ['id' => 1, 'start' => '10:00', 'end' => '15:00'],
            ['id' => 2, 'start' => '16:00', 'end' => '21:00'],
            ['id' => 3, 'start' => '12:00', 'end' => '16:00'],
            ['id' => 4, 'start' => '17:00', 'end' => '21:00'],
            ['id' => 5, 'start' => '12:00', 'end' => '20:00'],
            ['id' => 6, 'start' => '10:00', 'end' => '18:00'],
            ['id' => 7, 'start' => '14:00', 'end' => '22:00'],
            ['id' => 8, 'start' => '09:00', 'end' => '15:00'],
        ];
        WorkingHour::insert($times);
    }

    public function assignTimesDoctor()
    {
        $faker = Factory::create();
        $pair_ids = [[1, 2], [3, 4]];
        $clinics = Clinic::query()->whereHas('doctors', null, '=', 2)->get();
        $days = Day::query()->pluck('id');
        foreach ($clinics as $clinic) {
            $pair = $faker->randomElement($pair_ids);
            $doctors = $clinic->doctors;
            $nurses=$clinic->nurses;
            $i = 0;
            foreach ($doctors as $doctor) {
                $employee_id = $doctor->employee->id;
                foreach ($days as $day) {
                    DB::table('working_hour_employees')->insert([
                        'working_hour_id' => $pair[$i], 'employee_id' => $employee_id,
                        'day_id' => $day,
                    ]);
                }
            }
            $i=0;
            foreach ($nurses as $nurse) {
                $employee_id = $nurse->employee->id;
                foreach ($days as $day) {
                    DB::table('working_hour_employees')->insert([
                        'working_hour_id' => $pair[$i], 'employee_id' => $employee_id,
                        'day_id' => $day,
                    ]);
                }
            }
        }
        $full=$faker->randomElement([5, 6, 7, 8]);
        $clinics = Clinic::query()->whereHas('doctors', null, '=', 1)->get();
        foreach ($clinics as $clinic) {
            $employee_id = $clinic->doctor->employee->id;
            foreach ($days as $day) {
                DB::table('working_hour_employees')->insert([
                    'working_hour_id' => $full, 'employee_id' => $employee_id,
                    'day_id' => $day,
                ]);
            }
            $employee_id = $clinic->nurse->employee->id;
            foreach ($days as $day) {
                DB::table('working_hour_employees')->insert([
                    'working_hour_id' => $full, 'employee_id' => $employee_id,
                    'day_id' => $day,
                ]);
            }
        }
    }
}
