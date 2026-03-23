<?php

namespace App\Repositories;

use App\Models\PersonelHopital;
use App\Repositories\Interfaces\SecretaireRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SecretaireRepository implements SecretaireRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = PersonelHopital::secretaires()
            ->with(['serviceMedical', 'infosConnexion']);

        $query->search($filters['search'] ?? null);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        $query->byService($filters['service_id'] ?? null);

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
        return PersonelHopital::secretaires()
            ->with(['serviceMedical', 'infosConnexion'])
            ->find($id);
    }

    public function create(array $data): PersonelHopital
    {
        return PersonelHopital::create($data);
    }

    public function update(string $id, array $data): PersonelHopital
    {
        $secretaire = $this->findById($id);

        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        $secretaire->update($data);

        return $secretaire->fresh(['serviceMedical']);
    }

    public function delete(string $id): bool
    {
        $secretaire = $this->findById($id);

        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        return (bool) $secretaire->delete();
    }

    public function changerStatut(string $id, string $statut): PersonelHopital
    {
        $secretaire = $this->findById($id);

        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        $secretaire->update(['statut' => $statut]);

        return $secretaire->fresh(['serviceMedical']);
    }
}
