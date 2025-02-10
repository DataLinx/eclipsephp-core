<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => fake()->unique->domainName(),
            'name' => fake()->company(),
            'is_active' => fake()->boolean(),
            'is_secure' => fake()->boolean(),
        ];
    }
}
