<?php

namespace App\Repositories\Interfaces;

use App\Models\PersonelHopital;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SecretaireRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?PersonelHopital;

    public function create(array $data): PersonelHopital;

    public function update(string $id, array $data): PersonelHopital;

    public function delete(string $id): bool;

    public function changerStatut(string $id, string $statut): PersonelHopital;
}
