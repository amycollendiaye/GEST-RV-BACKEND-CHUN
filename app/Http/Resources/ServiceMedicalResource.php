<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceMedicalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'heure_ouverture' => $this->heure_ouverture,
            'heure_fermeture' => $this->heure_fermeture,
            'etat' => $this->etat,
            'medecins' => $this->whenLoaded('medecins', function () {
                return $this->medecins->map(fn ($medecin) => [
                    'id' => $medecin->id,
                    'matricule' => $medecin->matricule,
                    'nom' => $medecin->nom,
                    'prenom' => $medecin->prenom,
                    'specialite' => $medecin->specialite,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
