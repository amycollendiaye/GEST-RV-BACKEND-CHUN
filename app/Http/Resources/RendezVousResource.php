<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RendezVousResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'motif' => $this->motif,
            'statut' => $this->statut,
            'date_rendez_vous' => $this->date_rendez_vous?->toISOString(),
            'planning_medecin_id' => $this->planning_medecin_id,
            'service' => $this->whenLoaded('serviceMedical', function () {
                return [
                    'id' => $this->serviceMedical->id,
                    'nom' => $this->serviceMedical->nom,
                ];
            }),
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'nom' => $this->patient->nom,
                    'prenom' => $this->patient->prenom,
                    'matricule' => $this->patient->matricule,
                ];
            }),
            'medecin' => $this->whenLoaded('medecin', function () {
                if (!$this->medecin) {
                    return null;
                }

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
