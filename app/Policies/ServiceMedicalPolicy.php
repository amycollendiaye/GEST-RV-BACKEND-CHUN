<?php

namespace App\Policies;

use App\Models\PersonelHopital;
use App\Models\ServiceMedical;

class ServiceMedicalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(PersonelHopital $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(PersonelHopital $user, ServiceMedical $serviceMedical): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(PersonelHopital $user): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(PersonelHopital $user, ServiceMedical $serviceMedical): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(PersonelHopital $user, ServiceMedical $serviceMedical): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(PersonelHopital $user, ServiceMedical $serviceMedical): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(PersonelHopital $user, ServiceMedical $serviceMedical): bool
    {
        return $user->role === 'ADMIN';
    }
}
