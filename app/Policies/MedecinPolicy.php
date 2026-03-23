<?php

namespace App\Policies;

use App\Models\PersonelHopital;

class MedecinPolicy
{
    public function viewAny(PersonelHopital $user): bool
    {
        return in_array($user->role, ['ADMIN', 'SECRETAIRE'], true);
    }

    public function view(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        if (in_array($user->role, ['ADMIN', 'SECRETAIRE'], true)) {
            return true;
        }

        return $user->role === 'MEDECIN' && $user->id === $medecin->id;
    }

    public function create(PersonelHopital $user): bool
    {
        return $user->role === 'ADMIN';
    }

    public function update(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return $user->role === 'ADMIN';
    }

    public function delete(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return $user->role === 'ADMIN';
    }

    public function changerStatut(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return $user->role === 'ADMIN';
    }
}
