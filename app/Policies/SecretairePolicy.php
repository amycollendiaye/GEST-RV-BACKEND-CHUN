<?php

namespace App\Policies;

use App\Models\PersonelHopital;

class SecretairePolicy
{
    public function viewAny(PersonelHopital $user): bool
    {
        $role = strtoupper($user->role);
        return in_array($role, ['ADMIN', 'SECRETAIRE'], true);
    }

    public function view(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        $role = strtoupper($user->role);
        if (in_array($role, ['ADMIN', 'SECRETAIRE'], true)) {
            return true;
        }

        return $role === 'SECRETAIRE' && $user->id === $secretaire->id;
    }

    public function create(PersonelHopital $user): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function update(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function delete(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function changerStatut(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }
}
