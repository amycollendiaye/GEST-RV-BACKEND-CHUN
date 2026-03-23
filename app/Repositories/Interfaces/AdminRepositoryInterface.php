<?php

namespace App\Repositories\Interfaces;

use App\Models\PersonelHopital;

interface AdminRepositoryInterface
{
    public function adminExists(): bool;

    public function create(array $data): PersonelHopital;
}
