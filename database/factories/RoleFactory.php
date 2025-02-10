<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => \Eclipse\Core\Models\Site::factory(),
            'name' => fake()->name(),
            'guard_name' => fake()->text(255),
        ];
    }
}
