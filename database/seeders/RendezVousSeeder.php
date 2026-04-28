<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PersonnelHopital;
use App\Models\PlanningMedecin;
use App\Models\RendezVous;
use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RendezVousSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceMedical::query()->doesntExist()) {
            $this->call(ServiceMedicalSeeder::class);
        }

        if (Patient::query()->count() < 15) {
            $this->call(PatientSeeder::class);
        }

        if (PersonnelHopital::query()->where('role', 'MEDECIN')->doesntExist()) {
            $this->call(PersonnelHopitalSeeder::class);
        }

        $services = ServiceMedical::query()->orderBy('created_at')->take(5)->get()->values();
        $this->ensureEnoughMedecins($services);

        $medecins = PersonnelHopital::query()
            ->where('role', 'MEDECIN')
            ->where('statut', 'ACTIF')
            ->whereIn('service_medical_id', $services->pluck('id'))
            ->orderBy('created_at')
            ->get();

        $patients = Patient::query()
            ->where('statut', 'ACTIF')
            ->orderBy('created_at')
            ->get()
            ->values();

        if ($medecins->isEmpty() || $patients->isEmpty()) {
            return;
        }

        $motifs = [
            'Douleurs thoraciques a controler',
            'Suivi post consultation',
            'Bilan medical semestriel',
            'Controle tension arterielle',
            'Avis specialise',
            'Lecture de resultats d examens',
        ];

        RendezVous::withoutEvents(function () use ($medecins, $patients, $motifs): void {
            $patientIndex = 0;

            foreach ($medecins as $medecin) {
                $planningPasse = $this->createPlanning($medecin, now()->subDays(5));
                $planningProche = $this->createPlanning($medecin, now()->addDays(2));
                $planningLointain = $this->createPlanning($medecin, now()->addDays(6));

                $this->createRendezVousIfMissing([
                    'patient_id' => $patients[$patientIndex++ % $patients->count()]->id,
                    'service_medical_id' => $medecin->service_medical_id,
                    'medecin_id' => $medecin->id,
                    'planning_medecin_id' => $planningPasse->id,
                    'date_rendez_vous' => Carbon::parse($planningPasse->date->toDateString() . ' 09:00:00'),
                    'motif' => $motifs[array_rand($motifs)],
                    'statut' => 'FAIT',
                ]);

                $this->createRendezVousIfMissing([
                    'patient_id' => $patients[$patientIndex++ % $patients->count()]->id,
                    'service_medical_id' => $medecin->service_medical_id,
                    'medecin_id' => $medecin->id,
                    'planning_medecin_id' => $planningProche->id,
                    'date_rendez_vous' => Carbon::parse($planningProche->date->toDateString() . ' 10:30:00'),
                    'motif' => $motifs[array_rand($motifs)],
                    'statut' => 'PLANIFIER',
                ]);

                $this->createRendezVousIfMissing([
                    'patient_id' => $patients[$patientIndex++ % $patients->count()]->id,
                    'service_medical_id' => $medecin->service_medical_id,
                    'medecin_id' => $medecin->id,
                    'planning_medecin_id' => $planningLointain->id,
                    'date_rendez_vous' => Carbon::parse($planningLointain->date->toDateString() . ' 11:30:00'),
                    'motif' => $motifs[array_rand($motifs)],
                    'statut' => 'ANNULER',
                ]);
            }
        });
    }

    private function ensureEnoughMedecins(Collection $services): void
    {
        foreach ($services as $serviceIndex => $service) {
            $count = PersonnelHopital::query()
                ->where('role', 'MEDECIN')
                ->where('statut', 'ACTIF')
                ->where('service_medical_id', $service->id)
                ->count();

            for ($slot = $count + 1; $slot <= 2; $slot++) {
                PersonnelHopital::factory()->create([
                    'nom' => fake()->lastName(),
                    'prenom' => fake()->firstName(),
                    'email' => sprintf('medecin-seed-%d-%d@easeappointhub.test', $serviceIndex + 1, $slot),
                    'telephone' => sprintf('+221783%03d%03d', $serviceIndex + 1, $slot),
                    'matricule' => sprintf('MEDS%02d%02d', $serviceIndex + 1, $slot),
                    'role' => 'MEDECIN',
                    'statut' => 'ACTIF',
                    'specialite' => $service->nom,
                    'service_medical_id' => $service->id,
                ]);
            }
        }
    }

    private function createPlanning(PersonnelHopital $medecin, Carbon $date): PlanningMedecin
    {
        return PlanningMedecin::query()->firstOrCreate(
            [
                'medecin_id' => $medecin->id,
                'service_medical_id' => $medecin->service_medical_id,
                'date' => $date->toDateString(),
            ],
            [
                'heure_debut' => '08:00:00',
                'heure_fin' => '16:00:00',
                'heure_ouverture' => '08:00:00',
                'heure_fermeture' => '16:00:00',
                'capacite' => 6,
            ]
        );
    }

    private function createRendezVousIfMissing(array $attributes): void
    {
        $exists = RendezVous::query()
            ->where('patient_id', $attributes['patient_id'])
            ->where('medecin_id', $attributes['medecin_id'])
            ->where('planning_medecin_id', $attributes['planning_medecin_id'])
            ->where('date_rendez_vous', $attributes['date_rendez_vous'])
            ->exists();

        if ($exists) {
            return;
        }

        RendezVous::create($attributes);
    }
}
