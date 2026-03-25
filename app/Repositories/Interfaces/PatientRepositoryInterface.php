<?php

namespace App\Repositories\Interfaces;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?Patient;

    public function create(array $data): Patient;

    public function update(string $id, array $data): Patient;

    public function delete(string $id): bool;

    public function findByLogin(string $login): ?Patient;
}
