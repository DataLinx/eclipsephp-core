<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Enums\AddressType;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Address;
use Eclipse\World\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipient' => fake()->name(),
            'company_name' => fake()->company(),
            'company_vat_id' => fake()->numerify('##########'),
            'street_address' => [
                fake()->streetAddress(),
                fake()->streetAddress(),
            ],
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'type' => fake()->randomElement(AddressType::cases()),
            'country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory()->create()->id,
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }
}
