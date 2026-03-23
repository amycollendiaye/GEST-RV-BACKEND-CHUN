<?php

namespace App\Repositories\Interfaces;

use App\Models\ServiceMedical;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceMedicalRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?ServiceMedical;

    public function create(array $data): ServiceMedical;

    public function update(string $id, array $data): ServiceMedical;

    public function delete(string $id): bool;
}
