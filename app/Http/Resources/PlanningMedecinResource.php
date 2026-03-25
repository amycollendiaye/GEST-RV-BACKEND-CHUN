<?php

namespace App\Http\Resources;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanningMedecinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nombreAttribues = $this->attributed_rendez_vous_count ?? $this->attributedRendezVous()->count();
        $placesRestantes = max(0, (int) $this->capacite - (int) $nombreAttribues);

        return [
            'id' => $this->id,
            'date' => $this->date?->translatedFormat('d/m/Y') ?? $this->date?->format('d/m/Y'),
            'heure_ouverture' => $this->formatHeure($this->heure_ouverture),
            'heure_fermeture' => $this->formatHeure($this->heure_fermeture),
            'capacite' => $this->capacite,
            'nombre_rendez_vous_attribues' => $nombreAttribues,
            'places_restantes' => $placesRestantes,
            'est_complet' => $placesRestantes === 0,
            'medecin' => [
                'id' => $this->medecin?->id,
                'nom' => $this->medecin?->nom,
                'prenom' => $this->medecin?->prenom,
                'specialite' => $this->medecin?->specialite,
            ],
            'service' => [
                'id' => $this->serviceMedical?->id,
                'nom' => $this->serviceMedical?->nom,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function formatHeure($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i');
        }

        if ($value === null) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
