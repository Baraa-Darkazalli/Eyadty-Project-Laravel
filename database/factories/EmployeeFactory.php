<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'salary' => "{$this->faker->randomNumber(1, 20)}000",
            'previous_experience' => $this->faker->randomNumber(1, 15),
            'person_id' => function () {
                return Person::factory()->create()->id;
            },
        ];
    }
}
