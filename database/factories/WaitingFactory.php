<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Waiting>
 */
class WaitingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $patients_ids=\App\Models\Patient::query()->pluck('id');
        $doctors_ids=\App\Models\Doctor::query()->pluck('id');
        return [
            'appointment_id'=>1,
            'patient_id'=>$this->faker->unique()->randomElement($patients_ids),
            'doctor_id'=>$this->faker->randomElement($doctors_ids),
        ];
    }
}
