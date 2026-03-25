<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PersonelHopital;

class ConsultationPolicy
{
    public function viewAny(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital
            && in_array($user->role, ['MEDECIN', 'SECRETAIRE', 'ADMIN'], true);
    }

    public function view(PersonelHopital|Patient $user, Consultation $consultation): bool
    {
        if ($user instanceof Patient) {
            return $user->id === $consultation->patient_id;
        }

        return in_array($user->role, ['MEDECIN', 'SECRETAIRE', 'ADMIN'], true);
    }

    public function create(PersonelHopital|Patient $user): bool
    {
        return $user instanceof PersonelHopital && $user->role === 'MEDECIN';
    }

    public function update(PersonelHopital|Patient $user, Consultation $consultation): bool
    {
        return $user instanceof PersonelHopital
            && $user->role === 'MEDECIN'
            && $user->id === $consultation->medecin_id;
    }

    public function cloturer(PersonelHopital|Patient $user, Consultation $consultation): bool
    {
        return $this->update($user, $consultation);
    }

    public function reprogrammer(PersonelHopital|Patient $user, Consultation $consultation): bool
    {
        return $this->update($user, $consultation);
    }
}
