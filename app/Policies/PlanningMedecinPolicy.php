<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\PlanningMedecin;

class PlanningMedecinPolicy
{
    public function viewAny(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital
            && in_array($user->role, ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function view(PersonelHopital|Patient $user, PlanningMedecin $planning): bool
    {
        if (!$user instanceof PersonelHopital) {
            return false;
        }

        if (in_array($user->role, ['ADMIN', 'SECRETAIRE'], true)) {
            return true;
        }

        return $user->role === 'MEDECIN' && $planning->medecin_id === $user->id;
    }

    public function create(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital && $user->role === 'MEDECIN';
    }

    public function update(PersonelHopital|Patient $user, PlanningMedecin $planning): bool
    {
        return $user instanceof PersonelHopital
            && $user->role === 'MEDECIN'
            && $planning->medecin_id === $user->id
            && $planning->attributedRendezVous()->count() === 0;
    }

    public function delete(PersonelHopital|Patient $user, PlanningMedecin $planning): bool
    {
        return $user instanceof PersonelHopital
            && $user->role === 'MEDECIN'
            && $planning->medecin_id === $user->id
            && $planning->attributedRendezVous()->count() === 0;
    }
}
