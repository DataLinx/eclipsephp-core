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
            'id' => fake()->languageCode(),
            'name' => fake()->name(),
            'native_name' => fake()->text(50),
            'system_locale' => fake()->locale(),
            'is_active' => fake()->boolean(),
            'is_available_in_panel' => fake()->boolean(),
            'datetime_format' => fake()->text(20),
            'date_format' => fake()->text(10),
            'time_format' => fake()->text(10),
        ];
    }
}
