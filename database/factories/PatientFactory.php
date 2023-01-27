<?php

namespace Database\Factories;

use App\Models\BloodType;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'weight' => $this->faker->numberBetween(40, 120),
            'height' => $this->faker->numberBetween(150, 200),
            'person_id' => function () {
                return Person::factory()->create();
            },
            'blood_type_id' => $this->faker->randomElement(BloodType::query()->pluck('id')),
        ];
    }
}
