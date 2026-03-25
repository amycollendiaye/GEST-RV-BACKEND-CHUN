<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\RendezVous;

class RendezVousPolicy
{
    public function viewAny(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital
            && in_array($user->role, ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function view(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $rendezVous->patient_id;
        }

        return in_array($user->role, ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function create(PersonelHopital|Patient $user): bool
    {
        return $user instanceof Patient;
    }

    public function annuler(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        return $user instanceof Patient && $user->id === $rendezVous->patient_id;
    }

    public function changerStatut(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        return $user instanceof PersonelHopital
            && in_array($user->role, ['SECRETAIRE', 'MEDECIN'], true);
    }
}
