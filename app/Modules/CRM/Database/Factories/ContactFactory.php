<?php

namespace App\Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\CRM\Models\Contact;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['prospect', 'client', 'fournisseur']),
            'company_name' => $this->faker->boolean(70) ? $this->faker->company : null,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'country' => 'France',
            'status' => 'active',
        ];
    }
}
