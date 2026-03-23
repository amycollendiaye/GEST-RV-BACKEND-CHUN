<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceMedical>
 */
class ServiceMedicalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'id' => Str::uuid(),

            'nom' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),

            'heure_ouverture' => '08:00:00',
            'heure_fermeture' => '17:00:00',

            'etat' => $this->faker->randomElement([
                'DISPONIBLE',
                'INDISPONIBLE'
            ]),
        ];
    }
}
