<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Repositories\Interfaces\PatientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientRepository implements PatientRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Patient::query();

        $query->search($filters['search'] ?? null);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['date_debut'])) {
            $query->whereDate('created_at', '>=', $filters['date_debut']);
        }
        if (!empty($filters['date_fin'])) {
            $query->whereDate('created_at', '<=', $filters['date_fin']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['nom', 'prenom', 'matricule', 'created_at', 'date_naissance'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?Patient
    {
        return Patient::with(['dossierMedical'])->find($id);
    }

    public function findByLogin(string $login): ?Patient
    {
        return Patient::where('login', $login)->first();
    }

    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    public function update(string $id, array $data): Patient
    {
        $patient = $this->findById($id);

        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        $patient->update($data);

        return $patient->fresh();
    }

    public function delete(string $id): bool
    {
        $patient = $this->findById($id);

        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        return (bool) $patient->delete();
    }
}
