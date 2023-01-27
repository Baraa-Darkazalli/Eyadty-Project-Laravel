<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'previous_session_id'=>null,
            'is_review'=>0,
            'description'=>$this->faker->sentence(),
            'title'=>$this->faker->sentence(),
            // 'session_time'=>$this->faker->time(),
            // 'session_date'=>$this->faker->date()
        ];
    }
}
