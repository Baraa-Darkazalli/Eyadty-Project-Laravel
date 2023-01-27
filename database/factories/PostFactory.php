<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\ExtraService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $doctors_ids=Doctor::query()->pluck('id');
        return [
            'doctor_id'=>$this->faker->randomElement($doctors_ids),
            'blog_id'=>ExtraService::first()->id,
            'post_subject'=>$this->faker->sentence($this->faker->randomElement([1,2])),
            'body'=>$this->faker->paragraph($this->faker->randomElement([10,12,14]),true)
        ];
    }
}
