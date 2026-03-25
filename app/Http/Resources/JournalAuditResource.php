<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalAuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_action' => $this->type_action,
            'details' => $this->details ?? [],
            'adresse_ip' => $this->adresse_ip,
            'user_agent' => $this->user_agent,
            'auteur' => $this->auteur ? [
                'id' => $this->auteur->id,
                'nom' => $this->auteur->nom,
                'prenom' => $this->auteur->prenom,
                'matricule' => $this->auteur->matricule,
                'role' => $this->auteur->role,
            ] : null,
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }
}
