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
        $type = match (fake()->numberBetween(1, 3)) {
            1 => [AddressType::COMPANY_ADDRESS->value],
            2 => [AddressType::DEFAULT_ADDRESS->value],
            3 => [AddressType::COMPANY_ADDRESS->value, AddressType::DEFAULT_ADDRESS->value],
        };

        $hasCompanyAddress = in_array(AddressType::COMPANY_ADDRESS->value, $type);

        return [
            'recipient' => fake()->name(),
            'company_name' => $hasCompanyAddress ? fake()->company() : null,
            'company_vat_id' => $hasCompanyAddress ? fake()->numerify('##########') : null,
            'street_address' => [
                fake()->streetAddress(),
                fake()->streetAddress(),
            ],
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'type' => $type,
            'country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory()->create()->id,
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }
}
