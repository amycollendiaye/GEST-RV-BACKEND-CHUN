<?php

namespace Database\Factories;

use App\Models\ServiceMedical;
use App\Models\InfosConnexion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonnelHopital>
 */
class PersonnelHopitalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       
            $role = $this->faker->randomElement([
            'MEDECIN',
            'SECRETAIRE',
            'ADMIN'
        ]);

        return [
            'id' => Str::uuid(),

            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),

            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->phoneNumber(),

            'matricule' => strtoupper(Str::random(8)),

            'specialite' => $role === 'MEDECIN'
                ? $this->faker->randomElement(['Cardiologie', 'Pédiatrie'])
                : null,

            'role' => $role,
            'statut' => $this->faker->randomElement([
                'ACTIF',
                'INACTIF',
                'ENCONGE'
            ]),

            // ⚠️ logique métier
            'service_medical_id' => $role === 'ADMIN'
                ? null
                : ServiceMedical::inRandomOrder()->first()?->id,
        ];

        
    }

    public function configure()
    {
        return $this->afterCreating(function ($personnel) {
            $login = strtolower(str_replace('-', '', $personnel->matricule));

            InfosConnexion::create([
                'personel_hopital_id' => $personnel->id,
                'login' => $login,
                'password' => Hash::make('Password1!', ['rounds' => 12]),
                'first_login' => true,
            ]);
        });
    }
}
