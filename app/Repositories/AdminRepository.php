<?php

namespace App\Repositories;

use App\Models\PersonelHopital;
use App\Repositories\Interfaces\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    public function adminExists(): bool
    {
        return PersonelHopital::where('role', 'ADMIN')->exists();
    }

    public function create(array $data): PersonelHopital
    {
        return PersonelHopital::create($data);
    }
}
