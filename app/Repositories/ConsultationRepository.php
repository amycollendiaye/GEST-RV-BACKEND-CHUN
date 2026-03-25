<?php

namespace App\Repositories;

use App\Models\Consultation;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConsultationRepository implements ConsultationRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Consultation::with(['patient', 'medecin', 'rendezVous.serviceMedical']);

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                    ->orWhere('prenom', 'like', "%{$term}%")
                    ->orWhere('matricule', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['service_id'])) {
            $query->whereHas('rendezVous', function ($q) use ($filters) {
                $q->where('service_medical_id', $filters['service_id']);
            });
        }

        if (!empty($filters['medecin_id'])) {
            $query->where('medecin_id', $filters['medecin_id']);
        }

        if (!empty($filters['date_debut'])) {
            $query->whereDate('date_heure', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->whereDate('date_heure', '<=', $filters['date_fin']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['date_heure', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?Consultation
    {
        return Consultation::with(['patient', 'medecin', 'rendezVous.serviceMedical'])->find($id);
    }

    public function create(array $data): Consultation
    {
        return Consultation::create($data);
    }

    public function update(string $id, array $data): Consultation
    {
        $consultation = $this->findById($id);

        if (!$consultation) {
            abort(404, 'Consultation introuvable');
        }

        $consultation->update($data);

        return $consultation->fresh(['patient', 'medecin', 'rendezVous.serviceMedical']);
    }
}
