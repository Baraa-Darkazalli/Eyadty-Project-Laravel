<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CertificateSource>
 */
class CertificateSourceFactory extends Factory
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
            'source_name'=>$this->faker->sentence(2),
            'country_id'=>$this->faker->randomElement($countries_ids)
        ];
    }
}
