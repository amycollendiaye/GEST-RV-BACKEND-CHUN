<?php

namespace App\Policies;

use App\Models\DossierMedical;
use App\Models\Patient;
use App\Models\PersonelHopital;

class DossierMedicalPolicy
{
    public function view(PersonelHopital|Patient $user, DossierMedical $dossier): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $dossier->patient_id;
        }

        return in_array($user->role, ['MEDECIN', 'SECRETAIRE', 'ADMIN'], true);
    }

    public function update(PersonelHopital|Patient $user, DossierMedical $dossier): bool
    {
        return $user instanceof PersonelHopital && $user->role === 'MEDECIN';
    }
}
