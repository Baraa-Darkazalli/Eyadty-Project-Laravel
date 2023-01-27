<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PreviousWork>
 */
class PreviousWorkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $countries_ids=Country::query()->pluck('id');
        return [
            'work_name'=>$this->faker->jobTitle(),
            'work_source'=>$this->faker->sentence($this->faker->randomElement([1,2])),
            'employee_id'=>1,
            'country_id'=>$this->faker->randomElement($countries_ids)

        ];
    }
}
