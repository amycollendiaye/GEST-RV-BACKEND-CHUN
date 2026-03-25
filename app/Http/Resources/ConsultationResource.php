<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tension_artielle' => $this->tension_artielle,
            'poids' => $this->poids,
            'temperature' => $this->temperature,
            'sumptomes' => $this->sumptomes,
            'diagnostic' => $this->diagnostic,
            'traitement' => $this->traitement,
            'observations' => $this->observations,
            'date_heure' => $this->date_heure?->toISOString(),
            'statut' => $this->statut,
            'date_rendez_vous' => $this->rendezVous?->date_rendez_vous?->toISOString(),
            'statut_rendez' => $this->rendezVous?->statut,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'nom' => $this->patient->nom,
                    'prenom' => $this->patient->prenom,
                ];
            }),
            'medecin' => $this->whenLoaded('medecin', function () {
                return [
                    'id' => $this->medecin->id,
                    'nom' => $this->medecin->nom,
                    'prenom' => $this->medecin->prenom,
                    'specialite' => $this->medecin->specialite,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
