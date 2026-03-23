<?php

namespace App\Repositories;

use App\Models\PersonelHopital;
use App\Repositories\Interfaces\MedecinRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MedecinRepository implements MedecinRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = PersonelHopital::medecins()
            ->with(['serviceMedical', 'planningMedecins', 'infosConnexion']);

        $query->search($filters['search'] ?? null);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        $query->byService($filters['service_id'] ?? null);

        if (!empty($filters['specialite'])) {
            $query->where('specialite', $filters['specialite']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['nom', 'prenom', 'matricule', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?PersonelHopital
    {
        return PersonelHopital::medecins()
            ->with(['serviceMedical', 'planningMedecins', 'infosConnexion'])
            ->find($id);
    }

    public function create(array $data): PersonelHopital
    {
        return PersonelHopital::create($data);
    }

    public function update(string $id, array $data): PersonelHopital
    {
        $medecin = $this->findById($id);

        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        $medecin->update($data);

        return $medecin->fresh(['serviceMedical', 'planningMedecins']);
    }

    public function delete(string $id): bool
    {
        $medecin = $this->findById($id);

        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        return (bool) $medecin->delete();
    }

    public function changerStatut(string $id, string $statut): PersonelHopital
    {
        $medecin = $this->findById($id);

        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        $medecin->update(['statut' => $statut]);

        return $medecin->fresh(['serviceMedical', 'planningMedecins']);
    }
}
