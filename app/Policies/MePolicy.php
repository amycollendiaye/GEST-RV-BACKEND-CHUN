<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\PersonelHopital;
use Illuminate\Auth\Access\Response;

class MePolicy
{
    public function viewAdmin(PersonelHopital|Patient $user): Response
    {
        return $user instanceof PersonelHopital && $user->role === 'ADMIN'
            ? Response::allow()
            : Response::deny('Cet endpoint n\'est pas accessible pour votre role.');
    }

    public function viewMedecin(PersonelHopital|Patient $user): Response
    {
        return $user instanceof PersonelHopital && $user->role === 'MEDECIN'
            ? Response::allow()
            : Response::deny('Cet endpoint n\'est pas accessible pour votre role.');
    }

    public function viewSecretaire(PersonelHopital|Patient $user): Response
    {
        return $user instanceof PersonelHopital && $user->role === 'SECRETAIRE'
            ? Response::allow()
            : Response::deny('Cet endpoint n\'est pas accessible pour votre role.');
    }

    public function viewPatient(PersonelHopital|Patient $user): Response
    {
        return $user instanceof Patient
            ? Response::allow()
            : Response::deny('Cet endpoint n\'est pas accessible pour votre role.');
    }
}
