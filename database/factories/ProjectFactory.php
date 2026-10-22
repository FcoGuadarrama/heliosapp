<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    private static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        self::$sequence++;
        $statuses = ['draft', 'data_received', 'analyzing', 'analyzed', 'quoted', 'proposal_sent', 'won', 'lost', 'archived'];
        $installations = ['roof', 'ground', 'carport', 'other'];

        return [
            'client_id' => Client::factory(),
            'code' => 'PRY-'.date('Y').'-'.str_pad(self::$sequence, 4, '0', STR_PAD_LEFT),
            'name' => $this->faker->sentence(3),
            'status' => $this->faker->randomElement($statuses),
            'installation_type' => $this->faker->randomElement($installations),
            'property_type' => $this->faker->optional()->word(),
            'street' => $this->faker->streetAddress(),
            'exterior_number' => $this->faker->numerify('###'),
            'neighborhood' => $this->faker->citySuffix(),
            'municipality' => $this->faker->city(),
            'state' => 'CDMX',
            'postal_code' => $this->faker->postcode(),
            'latitude' => $this->faker->optional(0.3)->latitude(-19, -16),
            'longitude' => $this->faker->optional(0.3)->longitude(-102, -98),
            'objectives' => null,
            'property_data' => null,
            'notes' => $this->faker->optional()->paragraph(),
            'assigned_user_id' => User::factory()->state(['role' => 'engineer'])->create()->id,
            'created_by' => User::factory()->state(['role' => 'sales'])->create()->id,
        ];
    }
}
