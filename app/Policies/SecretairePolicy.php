<?php

namespace App\Policies;

use App\Models\PersonelHopital;

class SecretairePolicy
{
    public function viewAny(PersonelHopital $user): bool
    {
        return in_array($user->role, ['ADMIN', 'SECRETAIRE'], true);
    }

    public function view(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        if (in_array($user->role, ['ADMIN', 'SECRETAIRE'], true)) {
            return true;
        }

        return $user->role === 'SECRETAIRE' && $user->id === $secretaire->id;
    }

    public function create(PersonelHopital $user): bool
    {
        return $user->role === 'ADMIN';
    }

    public function update(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return $user->role === 'ADMIN';
    }

    public function delete(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return $user->role === 'ADMIN';
    }

    public function changerStatut(PersonelHopital $user, PersonelHopital $secretaire): bool
    {
        return $user->role === 'ADMIN';
    }
}
