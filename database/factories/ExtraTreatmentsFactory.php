<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExtraTreatments>
 */
class ExtraTreatmentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'treatment_name'=>$this->faker->word(),
            'item_price'=>$this->faker->randomNumber(5),
            'treatment_price'=>$this->faker->randomNumber(4),
            'description'=>$this->faker->sentence,
        ];
    }
}
