<?php

namespace App\Repositories\Interfaces;

use App\Models\Consultation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConsultationRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?Consultation;

    public function create(array $data): Consultation;

    public function update(string $id, array $data): Consultation;
}
