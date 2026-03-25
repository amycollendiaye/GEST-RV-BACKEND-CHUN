<?php

namespace App\Repositories;

use App\Models\PlanningMedecin;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlanningMedecinRepository implements PlanningMedecinRepositoryInterface
{
    public function paginateAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters);

        return $query->paginate($perPage);
    }

    public function paginateForMedecin(string $medecinId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->baseQuery()->where('medecin_id', $medecinId);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?PlanningMedecin
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): PlanningMedecin
    {
        return PlanningMedecin::create($data)->load(['medecin', 'serviceMedical']);
    }

    public function update(string $id, array $data): PlanningMedecin
    {
        $planning = $this->findById($id);

        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        $planning->update($data);

        return $planning->fresh(['medecin', 'serviceMedical']);
    }

    public function delete(string $id): bool
    {
        $planning = PlanningMedecin::find($id);

        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        return (bool) $planning->delete();
    }

    public function existsForDate(string $medecinId, string $serviceId, string $date, ?string $ignoreId = null): bool
    {
        return PlanningMedecin::query()
            ->where('medecin_id', $medecinId)
            ->where('service_medical_id', $serviceId)
            ->whereDate('date', $date)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function countAttributedRendezVous(string $planningId): int
    {
        return PlanningMedecin::query()
            ->findOrFail($planningId)
            ->attributedRendezVous()
            ->count();
    }

    public function paginateRendezVous(string $planningId, int $perPage): LengthAwarePaginator
    {
        $planning = $this->findById($planningId);

        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        return $planning->rendezVous()
            ->with(['patient', 'serviceMedical', 'medecin'])
            ->whereNull('deleted_at')
            ->orderBy('date_rendez_vous')
            ->paginate($perPage);
    }

    public function findClosestAvailableForService(string $serviceId): ?PlanningMedecin
    {
        return PlanningMedecin::query()
            ->with(['medecin', 'serviceMedical'])
            ->withCount(['attributedRendezVous as attributed_rendez_vous_count'])
            ->where('service_medical_id', $serviceId)
            ->whereDate('date', '>', now()->toDateString())
            ->whereHas('medecin', function ($query) use ($serviceId) {
                $query->where('role', 'MEDECIN')
                    ->where('statut', 'ACTIF')
                    ->where('service_medical_id', $serviceId);
            })
            ->whereRaw(
                "(select count(*) from rendez_vous rv
                    where rv.planning_medecin_id = planning_medecins.id
                      and rv.deleted_at is null
                      and rv.statut != ?) < planning_medecins.capacite",
                ['ANNULER']
            )
            ->orderBy('date')
            ->orderBy('heure_ouverture')
            ->first();
    }

    private function baseQuery()
    {
        return PlanningMedecin::query()
            ->with(['medecin', 'serviceMedical'])
            ->withCount(['attributedRendezVous as attributed_rendez_vous_count']);
    }

    private function applyFilters($query, array $filters): void
    {
        $query->byService($filters['service_id'] ?? null)
            ->byMedecin($filters['medecin_id'] ?? null)
            ->dateBetween($filters['date_debut'] ?? null, $filters['date_fin'] ?? null);

        if (array_key_exists('disponible', $filters) && $filters['disponible'] !== null && $filters['disponible'] !== '') {
            $value = filter_var($filters['disponible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === true) {
                $query->whereRaw(
                    "(select count(*) from rendez_vous rv
                        where rv.planning_medecin_id = planning_medecins.id
                          and rv.deleted_at is null
                          and rv.statut != ?) < planning_medecins.capacite",
                    ['ANNULER']
                );
            }
            if ($value === false) {
                $query->whereRaw(
                    "(select count(*) from rendez_vous rv
                        where rv.planning_medecin_id = planning_medecins.id
                          and rv.deleted_at is null
                          and rv.statut != ?) >= planning_medecins.capacite",
                    ['ANNULER']
                );
            }
        }
    }

    private function applySort($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'date';
        $sortDir = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['date', 'heure_ouverture', 'capacite', 'created_at'];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'date';
        }

        $query->orderBy($sortBy, $sortDir);
    }
}
