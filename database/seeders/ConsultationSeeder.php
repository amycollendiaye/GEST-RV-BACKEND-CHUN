<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    public function run(): void
    {
        if (RendezVous::query()->where('statut', 'FAIT')->doesntExist()) {
            $this->call(RendezVousSeeder::class);
        }

        $rendezVousTermines = RendezVous::query()
            ->with(['patient', 'medecin', 'consultation'])
            ->where('statut', 'FAIT')
            ->whereNotNull('medecin_id')
            ->get();

        Consultation::withoutEvents(function () use ($rendezVousTermines): void {
            foreach ($rendezVousTermines as $rendezVous) {
                if ($rendezVous->consultation !== null) {
                    continue;
                }

                Consultation::create([
                    'rendez_vous_id' => $rendezVous->id,
                    'patient_id' => $rendezVous->patient_id,
                    'medecin_id' => $rendezVous->medecin_id,
                    'tension_artielle' => fake()->randomElement(['12/8', '13/8', '11/7', '14/9']),
                    'poids' => fake()->randomFloat(2, 48, 96),
                    'temperature' => fake()->randomFloat(1, 36, 39),
                    'sumptomes' => fake()->randomElement([
                        'Fatigue generale et maux de tete',
                        'Douleurs diffuses et essoufflement leger',
                        'Palpitations intermittentes',
                        'Fievre moderee avec courbatures',
                    ]),
                    'diagnostic' => fake()->randomElement([
                        'Etat clinique stable avec surveillance recommandee',
                        'Infection benigne traitee en ambulatoire',
                        'Syndrome douloureux necessitant un suivi specialise',
                        'Desequilibre tensionnel a reevaluer',
                    ]),
                    'traitement' => fake()->randomElement([
                        'Paracetamol et repos pendant 5 jours',
                        'Traitement symptomatique et controle dans une semaine',
                        'Prescription d examens complementaires',
                        'Adaptation du traitement habituel',
                    ]),
                    'observations' => fake()->optional()->sentence(),
                    'date_heure' => Carbon::parse($rendezVous->date_rendez_vous)->addMinutes(45),
                    'statut' => 'FAIT',
                ]);
            }
        });
    }
}
