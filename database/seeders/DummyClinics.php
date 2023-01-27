<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicName;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Nurse;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DummyClinics extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->clinicDepartments();
        $this->clinic_names();
        $this->createClinicsDoctors(ClinicName::count());
    }

    public function clinicDepartments()
    {
        Department::insert([
            'name' => 'Specialized Clinics',
        ]);
    }

    public function clinic_names()
    {
        $department_id = Department::where('name', 'Specialized Clinics')->first()->id;
        $clinic_names =
            [
                ['name' => 'Heart Clinic', 'department_id' => $department_id],
                ['name' => 'Eye Clinic', 'department_id' => $department_id],
                ['name' => 'Skin Clinic', 'department_id' => $department_id],
                ['name' => 'Children Clinic', 'department_id' => $department_id],
                ['name' => 'Women Clinic', 'department_id' => $department_id],
                ['name' => 'Orthopedic Clinic', 'department_id' => $department_id],
                ['name' => 'Urology Clinic', 'department_id' => $department_id],
                ['name' => 'Gastrointestinal Clinic', 'department_id' => $department_id],
                ['name' => 'Cosmetic Clinic', 'department_id' => $department_id],
                ['name' => 'Neurological Clinic', 'department_id' => $department_id],
                ['name' => 'Psychiatric Clinic', 'department_id' => $department_id],

            ];
        ClinicName::insert($clinic_names);
    }

    public function createClinicsDoctors($x = 1)
    {
        $faker = Factory::create();
        while ($x != 0) {
            $y=$faker->randomElement([1,2]);
            Clinic::factory()->has(Doctor::factory()->count($y))->has(Nurse::factory()->count($y))->create();
            $x--;
        }
    }
}
