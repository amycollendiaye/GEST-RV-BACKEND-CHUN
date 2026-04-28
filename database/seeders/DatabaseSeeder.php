<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceMedicalSeeder::class,
            PersonnelHopitalSeeder::class,
            PatientSeeder::class,
            RendezVousSeeder::class,
            ConsultationSeeder::class,
        ]);
    }
}
