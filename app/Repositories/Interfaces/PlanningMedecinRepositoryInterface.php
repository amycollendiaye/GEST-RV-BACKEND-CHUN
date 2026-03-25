<?php

namespace App\Repositories\Interfaces;

use App\Models\PlanningMedecin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PlanningMedecinRepositoryInterface
{
    public function paginateAll(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateForMedecin(string $medecinId, array $filters, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?PlanningMedecin;

    public function create(array $data): PlanningMedecin;

    public function update(string $id, array $data): PlanningMedecin;

    public function delete(string $id): bool;

    public function existsForDate(string $medecinId, string $serviceId, string $date, ?string $ignoreId = null): bool;

    public function countAttributedRendezVous(string $planningId): int;

    public function paginateRendezVous(string $planningId, int $perPage): LengthAwarePaginator;

    public function findClosestAvailableForService(string $serviceId): ?PlanningMedecin;
}
