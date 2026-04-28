<?php

namespace Database\Seeders;

use App\Models\ServiceMedical;
use Illuminate\Database\Seeder;

class ServiceMedicalSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceMedical::query()->exists()) {
            return;
        }

        $services = [
            [
                'nom' => 'Medecine generale',
                'description' => 'Consultations de premiere intention et suivi medical general.',
                'heure_ouverture' => '08:00:00',
                'heure_fermeture' => '17:00:00',
                'etat' => 'DISPONIBLE',
            ],
            [
                'nom' => 'Cardiologie',
                'description' => 'Prise en charge des pathologies cardiovasculaires et bilans specialises.',
                'heure_ouverture' => '08:00:00',
                'heure_fermeture' => '16:30:00',
                'etat' => 'DISPONIBLE',
            ],
            [
                'nom' => 'Pediatrie',
                'description' => 'Consultations et suivi medical des nourrissons, enfants et adolescents.',
                'heure_ouverture' => '08:30:00',
                'heure_fermeture' => '17:00:00',
                'etat' => 'DISPONIBLE',
            ],
            [
                'nom' => 'Gynecologie',
                'description' => 'Suivi gynecologique, prevention et consultations specialisees.',
                'heure_ouverture' => '09:00:00',
                'heure_fermeture' => '17:30:00',
                'etat' => 'DISPONIBLE',
            ],
            [
                'nom' => 'Dermatologie',
                'description' => 'Diagnostic et traitement des affections de la peau.',
                'heure_ouverture' => '08:00:00',
                'heure_fermeture' => '15:30:00',
                'etat' => 'DISPONIBLE',
            ],
            [
                'nom' => 'Radiologie',
                'description' => 'Imagerie medicale et examens de diagnostic complementaires.',
                'heure_ouverture' => '08:00:00',
                'heure_fermeture' => '18:00:00',
                'etat' => 'INDISPONIBLE',
            ],
        ];

        foreach ($services as $service) {
            ServiceMedical::create($service);
        }
    }
}
