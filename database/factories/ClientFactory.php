<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['residential', 'commercial', 'industrial', 'agricultural'];

        return [
            'type' => $this->faker->randomElement($types),
            'name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->numerify('##########'),
            'rfc' => strtoupper($this->faker->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}')),
            'street' => $this->faker->streetAddress(),
            'exterior_number' => $this->faker->numerify('###'),
            'interior_number' => $this->faker->optional()->numerify('##'),
            'neighborhood' => $this->faker->citySuffix(),
            'municipality' => $this->faker->city(),
            'state' => 'CDMX',
            'postal_code' => $this->faker->numerify('#####'),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => null,
        ];
    }
}
