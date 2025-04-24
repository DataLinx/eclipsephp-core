<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\WorldCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorldCountryFactory extends Factory
{
    protected $model = WorldCountry::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
            'code' => $this->faker->unique()->countryCode,
            'region_id' => null, // Will be set manually or in test
        ];
    }
}
