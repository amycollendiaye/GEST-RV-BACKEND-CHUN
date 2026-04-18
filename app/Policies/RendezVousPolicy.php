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
            && in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function view(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $rendezVous->patient_id;
        }

        return in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function create(PersonelHopital|Patient $user): bool
    {
        return $user instanceof Patient || ($user instanceof PersonelHopital && strtoupper($user->role) === 'SECRETAIRE');
    }

    public function update(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        return $user instanceof PersonelHopital
            && in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE'], true);
    }

    public function annuler(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $rendezVous->patient_id;
        }

        return in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE'], true);
    }

    public function changerStatut(PersonelHopital|Patient $user, RendezVous $rendezVous): bool
    {
        return $user instanceof PersonelHopital
            && in_array(strtoupper($user->role), ['SECRETAIRE', 'MEDECIN', 'ADMIN'], true);
    }
}
