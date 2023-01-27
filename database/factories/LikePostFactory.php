<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LikePost>
 */
class LikePostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $users_ids=User::query()->pluck('id');
        return [
            'post_id'=>Post::first()->id,
            'user_id'=>$this->faker->unique(true)->randomElement($users_ids)
        ];
    }
}
