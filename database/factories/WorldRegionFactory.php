<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\WorldRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorldRegionFactory extends Factory
{
    protected $model = WorldRegion::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country,
            'code' => $this->faker->unique()->countryCode,
            'parent_id' => null,
            'is_special' => false,
        ];
    }

    public function special(): self
    {
        return $this->state(fn () => ['is_special' => true]);
    }
}
