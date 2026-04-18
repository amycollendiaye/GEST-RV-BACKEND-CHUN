<?php

namespace App\Policies;

use App\Models\PersonelHopital;

class MedecinPolicy
{
    public function viewAny(PersonelHopital $user): bool
    {
        $role = strtoupper($user->role);
        \Illuminate\Support\Facades\Log::info('Tentative accès liste médecins', [
            'user_id' => $user->id,
            'role_original' => $user->role,
            'role_majuscule' => $role
        ]);
        return in_array($role, ['ADMIN', 'SECRETAIRE'], true);
    }

    public function view(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        $role = strtoupper($user->role);
        if (in_array($role, ['ADMIN', 'SECRETAIRE'], true)) {
            return true;
        }

        return $role === 'MEDECIN' && $user->id === $medecin->id;
    }

    public function create(PersonelHopital $user): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function update(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function delete(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }

    public function changerStatut(PersonelHopital $user, PersonelHopital $medecin): bool
    {
        return strtoupper($user->role) === 'ADMIN';
    }
}
