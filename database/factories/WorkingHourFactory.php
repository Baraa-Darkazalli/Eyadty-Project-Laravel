<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $start = $this->faker->numberBetween(8, 12);
        $end = $this->faker->numberBetween(16, 20);

        return [
            'start' => "{$start}:00",
            'end' => "{$end}:00",
        ];
    }
}
