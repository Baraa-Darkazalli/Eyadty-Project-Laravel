<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'doctor_id'=>$this->faker->randomElement(Doctor::query()->pluck('id')),
            'patient_id'=>$this->faker->randomElement(Patient::query()->pluck('id')),
            'appointment_statue_id'=>1,
            'appointment_time'=>$this->faker->dateTime()->format('H:i'),
            'appointment_date'=>$this->faker->dateTimeBetween('+1 days','+31 days')->format('Y-m-d'),
            'is_review'=>$this->faker->randomElement([1,0]),

        ];
    }
}
