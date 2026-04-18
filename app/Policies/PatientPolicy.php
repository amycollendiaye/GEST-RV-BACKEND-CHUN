<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\PersonelHopital;

class PatientPolicy
{
    public function viewAny(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital
            && in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE'], true);
    }

    public function view(PersonelHopital|Patient $user, Patient $patient): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $patient->id;
        }

        return in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE', 'MEDECIN'], true);
    }

    public function create(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital 
            && in_array(strtoupper($user->role), ['ADMIN', 'SECRETAIRE'], true);
    }

    public function update(PersonelHopital|Patient $user, Patient $patient): bool
    {
        return $user instanceof PersonelHopital
            && in_array(strtoupper($user->role), ['SECRETAIRE', 'ADMIN'], true);
    }

    public function delete(PersonelHopital|Patient $user, Patient $patient): bool
    {
        return $user instanceof PersonelHopital && strtoupper($user->role) === 'ADMIN';
    }
}
