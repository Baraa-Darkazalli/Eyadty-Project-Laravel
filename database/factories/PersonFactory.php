<?php

namespace Database\Factories;

use App\Models\Name;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public $x;

    public $first_name;

    public function definition()
    {
        $faker = FakerFactory::create();
        $this->x = $faker->randomElement([0, 1]);
        $this->first_name = $this->x == 1 ? $faker->firstNameMale : $faker->firstNameFemale;

        return [
            'gender' => $this->x,
            'birth_date' => $this->faker->dateTimeBetween('1970-01-01', '2015-01-01'),
            'name_id' => function () {
                return Name::factory()->create(['first_name' => $this->first_name])->id;
            },
        ];
    }
}
