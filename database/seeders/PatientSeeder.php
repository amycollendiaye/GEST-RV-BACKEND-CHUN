<?php

namespace Database\Seeders;

use App\Models\DossierMedical;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        if (Patient::query()->count() >= 15) {
            return;
        }

        $groupesSanguins = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $allergies = ['Aucune', 'Penicilline', 'Arachides', 'Poussiere', 'Latex'];
        $maladiesChroniques = ['Aucune', 'Hypertension', 'Diabete de type 2', 'Asthme', 'Migraine chronique'];
        $traitements = ['Aucun', 'Paracetamol', 'Metformine', 'Ventoline', 'Traitement antihypertenseur'];

        for ($index = 1; $index <= 15; $index++) {
            $patient = Patient::create([
                'nom' => fake()->lastName(),
                'prenom' => fake()->firstName(),
                'email' => sprintf('patient%02d@easeappointhub.test', $index),
                'telephone' => sprintf('+22177000%04d', $index),
                'date_naissance' => fake()->dateTimeBetween('-75 years', '-18 years')->format('Y-m-d'),
                'adresse' => fake()->address(),
                'matricule' => sprintf('PAT%04d', $index),
                'login' => sprintf('pat%04d', $index),
                'password' => Hash::make('Password1!'),
                'first_login' => false,
                'statut' => $index % 6 === 0 ? 'INACTIF' : 'ACTIF',
                'activation_token' => null,
                'activation_token_expires_at' => null,
            ]);

            DossierMedical::create([
                'numero_dossier' => sprintf('DM-%04d', $index),
                'patient_id' => $patient->id,
                'groupe_sanguin' => $groupesSanguins[array_rand($groupesSanguins)],
                'antecedents_medicaux' => fake()->randomElement([
                    'Aucun antecedent significatif',
                    'Antcedents d hypertension arterielle',
                    'Suivi pour diabete equilibre',
                    'Episodes recurrents de migraine',
                ]),
                'antecedents_chirurgicaux' => fake()->randomElement([
                    'Aucun antecedent chirurgical',
                    'Appendicectomie ancienne',
                    'Cesarienne en 2018',
                ]),
                'antecedents_familiaux' => fake()->randomElement([
                    'Aucun antecedent familial notable',
                    'Antecedents familiaux de diabete',
                    'Antecedents familiaux d hypertension',
                ]),
                'allergies' => $allergies[array_rand($allergies)],
                'maladies_chroniques' => $maladiesChroniques[array_rand($maladiesChroniques)],
                'traitements_en_cours' => $traitements[array_rand($traitements)],
            ]);
        }
    }
}
