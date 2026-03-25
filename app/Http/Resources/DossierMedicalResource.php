<?php

namespace App\Http\Resources;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DossierMedicalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isPatient = $user instanceof Patient;

        $patient = $this->patient;
        $consultations = $this->consultations ?? collect();
        $lastConsultation = $consultations->sortByDesc('date_heure')->first();

        $rendezVous = $patient?->rendezVous ?? collect();

        $dateDerniereConsultation = $lastConsultation?->date_heure?->toDateString();
        $datePremierRendezVous = $rendezVous->sortBy('date_rendez_vous')->first()?->date_rendez_vous?->toDateString();

        return [
            'identification' => [
                'id' => $this->id,
                'numero_dossier' => $this->numero_dossier,
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
            'patient' => [
                'id' => $patient?->id,
                'matricule' => $patient?->matricule,
                'nom' => $patient?->nom,
                'prenom' => $patient?->prenom,
                'email' => $patient?->email,
                'telephone' => $patient?->telephone,
                'date_naissance' => $patient?->date_naissance?->toDateString(),
                'age' => $patient?->date_naissance
                    ? Carbon::parse($patient->date_naissance)->age
                    : null,
                'adresse' => $patient?->adresse,
            ],
            'informations_medicales' => [
                'groupe_sanguin' => $this->groupe_sanguin,
                'antecedents_medicaux' => $this->antecedents_medicaux,
                'antecedents_chirurgicaux' => $this->antecedents_chirurgicaux,
                'antecedents_familiaux' => $this->antecedents_familiaux,
                'allergies' => $this->allergies,
                'maladies_chroniques' => $this->maladies_chroniques,
                'traitements_en_cours' => $this->traitements_en_cours,
            ],
            'statistiques' => [
                'nombre_consultations' => $consultations->count(),
                'nombre_rendez_vous_total' => $rendezVous->count(),
                'nombre_rendez_vous_annules' => $rendezVous->where('statut', 'ANNULER')->count(),
                'date_derniere_consultation' => $dateDerniereConsultation,
                'date_premier_rendez_vous' => $datePremierRendezVous,
            ],
            'derniere_consultation' => $lastConsultation
                ? $this->formatDerniereConsultation($lastConsultation, $isPatient)
                : null,
        ];
    }

    private function formatDerniereConsultation($consultation, bool $isPatient): array
    {
        $medecin = $consultation->medecin;
        $service = $consultation->rendezVous?->serviceMedical;

        $base = [
            'date_heure' => $consultation->date_heure?->toISOString(),
            'medecin' => [
                'nom' => $medecin?->nom,
                'prenom' => $medecin?->prenom,
            ],
            'service' => $service ? ['nom' => $service->nom] : null,
            'sumptomes' => $consultation->sumptomes,
        ];

        if ($isPatient) {
            return $base;
        }

        return array_merge($base, [
            'id' => $consultation->id,
            'statut' => $consultation->rendezVous?->statut,
            'tension_artielle' => $consultation->tension_artielle,
            'poids' => $consultation->poids,
            'temperature' => $consultation->temperature,
            'diagnostic' => $consultation->diagnostic,
            'traitement' => $consultation->traitement,
            'observations' => $consultation->observations,
        ]);
    }
}
