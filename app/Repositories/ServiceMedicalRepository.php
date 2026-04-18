<?php

namespace App\Repositories;

use App\Models\ServiceMedical;
use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceMedicalRepository implements ServiceMedicalRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = ServiceMedical::query()->withCount('medecins');

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->whereRaw('LOWER(nom) LIKE ?', ["%" . strtolower($term) . "%"]);
        }

        if (!empty($filters['statut'])) {
            $query->where('etat', $filters['statut']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['nom', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?ServiceMedical
    {
        return ServiceMedical::with(['medecins'])->find($id);
    }

    public function create(array $data): ServiceMedical
    {
        return ServiceMedical::create($data);
    }

    public function update(string $id, array $data): ServiceMedical
    {
        $service = $this->findById($id);

        if (!$service) {
            abort(404, 'Service médical introuvable');
        }

        $service->update($data);

        return $service->fresh(['medecins']);
    }

    public function delete(string $id): bool
    {
        $service = $this->findById($id);

        if (!$service) {
            abort(404, 'Service médical introuvable');
        }

        return (bool) $service->delete();
    }
}
