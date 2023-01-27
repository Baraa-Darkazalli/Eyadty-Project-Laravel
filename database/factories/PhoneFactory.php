<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phone>
 */
class PhoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $person_ids = Person::all()->pluck('id');

        return [
            'phone_number' => $this->faker->phoneNumber,
            'person_id' => $this->faker->unique()->randomElement($person_ids),
        ];
    }
}
