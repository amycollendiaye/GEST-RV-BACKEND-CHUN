<?php

namespace App\Repositories\Interfaces;

use App\Models\RendezVous;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RendezVousRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findAllByPatient(string $patientId, array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?RendezVous;

    public function create(array $data): RendezVous;

    public function update(string $id, array $data): RendezVous;

    public function paginateByPlanning(string $planningId, int $perPage): LengthAwarePaginator;
}
