<?php

namespace Database\Factories;

use App\Models\ClinicName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $clinic_names_id = ClinicName::query()->pluck('id');

        return [
            'clinic_name_id' => $this->faker->unique()->randomElement($clinic_names_id),
            'session_price' => "{$this->faker->randomElement([10, 15, 20, 25, 30])}000",
        ];
    }
}
