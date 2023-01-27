<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Employee;
use App\Models\SessionDuration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        // $clinic_ids=Clinic::query()->select('id')->pluck('id','id');
        // $clinic_ids=collect($clinic_ids)->values();
        // $clinic_ids=json_decode($clinic_ids);
        // $clinic_ids=array_merge($clinic_ids,$clinic_ids);
        $sessions_duration = SessionDuration::query()->pluck('id');

        return [

            'session_duration_id' => $this->faker->randomElement($sessions_duration),
            'salary_rate' => $this->faker->randomFloat(2, 0.25, 0.75),
            'employee_id' => function () {
                return Employee::factory()->create()->id;
            },
        ];
    }
}
