<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretaireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'statut' => $this->statut,
            'role' => $this->role,
            'first_login' => $this->infosConnexion?->first_login,
            'service' => $this->serviceMedical ? [
                'id' => $this->serviceMedical->id,
                'nom' => $this->serviceMedical->nom,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
