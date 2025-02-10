<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Locale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'native_name' => fake()->text(255),
            'system_locale' => fake()->text(255),
            'is_active' => fake()->boolean(),
            'is_available_in_panel' => fake()->boolean(),
            'datetime_format' => fake()->text(255),
            'date_format' => fake()->text(255),
            'time_format' => fake()->text(255),
        ];
    }
}
