<?php

namespace Database\Seeders;

use App\Models\PersonnelHopital;
use App\Models\ServiceMedical;
use Illuminate\Database\Seeder;

class PersonnelHopitalSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceMedical::query()->doesntExist()) {
            $this->call(ServiceMedicalSeeder::class);
        }

        if (PersonnelHopital::query()->exists()) {
            return;
        }

        $services = ServiceMedical::query()->orderBy('created_at')->get()->values();

        PersonnelHopital::factory()->create([
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'email' => 'admin@easeappointhub.test',
            'telephone' => '+221780000001',
            'matricule' => 'ADM0001',
            'role' => 'ADMIN',
            'statut' => 'ACTIF',
            'specialite' => null,
            'service_medical_id' => null,
        ]);

        foreach ($services as $index => $service) {
            PersonnelHopital::factory()->create([
                'nom' => fake()->lastName(),
                'prenom' => fake()->firstName(),
                'email' => sprintf('medecin%02d@easeappointhub.test', $index + 1),
                'telephone' => sprintf('+22178100%04d', $index + 1),
                'matricule' => sprintf('MED%04d', $index + 1),
                'role' => 'MEDECIN',
                'statut' => 'ACTIF',
                'specialite' => $service->nom,
                'service_medical_id' => $service->id,
            ]);

            PersonnelHopital::factory()->create([
                'nom' => fake()->lastName(),
                'prenom' => fake()->firstName(),
                'email' => sprintf('secretaire%02d@easeappointhub.test', $index + 1),
                'telephone' => sprintf('+22178200%04d', $index + 1),
                'matricule' => sprintf('SEC%04d', $index + 1),
                'role' => 'SECRETAIRE',
                'statut' => 'ACTIF',
                'specialite' => null,
                'service_medical_id' => $service->id,
            ]);
        }
    }
}
