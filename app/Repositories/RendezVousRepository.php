<?php

namespace App\Repositories;

use App\Models\RendezVous;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RendezVousRepository implements RendezVousRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = RendezVous::with(['patient', 'serviceMedical', 'medecin', 'planningMedecin']);

        $this->applyFilters($query, $filters);

        $this->applySort($query, $filters);

        return $query->paginate($perPage);
    }

    public function findAllByPatient(string $patientId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = RendezVous::with(['patient', 'serviceMedical', 'medecin', 'planningMedecin'])
            ->where('patient_id', $patientId);

        $this->applyFilters($query, $filters);

        $this->applySort($query, $filters);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?RendezVous
    {
        return RendezVous::with(['patient', 'serviceMedical', 'medecin', 'consultation', 'planningMedecin'])->find($id);
    }

    public function create(array $data): RendezVous
    {
        return RendezVous::create($data)->load(['patient', 'serviceMedical', 'medecin', 'planningMedecin']);
    }

    public function update(string $id, array $data): RendezVous
    {
        $rendezVous = $this->findById($id);

        if (!$rendezVous) {
            abort(404, 'Rendez-vous introuvable');
        }

        $rendezVous->update($data);

        return $rendezVous->fresh(['patient', 'serviceMedical', 'medecin', 'consultation', 'planningMedecin']);
    }

    public function paginateByPlanning(string $planningId, int $perPage): LengthAwarePaginator
    {
        return RendezVous::with(['patient', 'serviceMedical', 'medecin', 'planningMedecin'])
            ->where('planning_medecin_id', $planningId)
            ->whereNull('deleted_at')
            ->orderBy('date_rendez_vous')
            ->paginate($perPage);
    }

    private function applyFilters($query, array $filters): void
    {
        $query->search($filters['search'] ?? null);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['service_id'])) {
            $query->where('service_medical_id', $filters['service_id']);
        }

        if (!empty($filters['medecin_id'])) {
            $query->where('medecin_id', $filters['medecin_id']);
        }

        if (!empty($filters['date_debut'])) {
            $query->whereDate('date_rendez_vous', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->whereDate('date_rendez_vous', '<=', $filters['date_fin']);
        }
    }

    private function applySort($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['date_rendez_vous', 'statut', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
