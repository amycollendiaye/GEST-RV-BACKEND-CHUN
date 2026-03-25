<?php

namespace App\Repositories;

use App\Models\JournalAudit;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Schema;

class JournalAuditRepository implements JournalAuditRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $this->ensureTableExists();

        return $this->query($filters)->paginate($perPage);
    }

    public function findById(int $id): ?JournalAudit
    {
        $this->ensureTableExists();

        return JournalAudit::with(['auteur.infosConnexion'])->find($id);
    }

    public function create(array $data): JournalAudit
    {
        $this->ensureTableExists();

        return JournalAudit::create($data);
    }

    public function cursor(array $filters): LazyCollection
    {
        $this->ensureTableExists();

        return $this->query($filters)->lazy();
    }

    private function query(array $filters): Builder
    {
        $query = JournalAudit::query()->with(['auteur.infosConnexion']);

        if (!empty($filters['type_action'])) {
            $query->where('type_action', $filters['type_action']);
        }

        if (!empty($filters['personel_id'])) {
            $query->where('personel_hopital_id', $filters['personel_id']);
        }

        if (!empty($filters['date_debut'])) {
            $query->whereDate('created_at', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->whereDate('created_at', '<=', $filters['date_fin']);
        }

        if (!empty($filters['adresse_ip'])) {
            $query->where('adresse_ip', $filters['adresse_ip']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->whereRaw('details::text ILIKE ?', ['%' . $search . '%']);
        }

        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $sortDir);
    }

    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('journal_audits')) {
                abort(503, 'La table journal_audits est introuvable. Exécutez la migration avant d’utiliser ces endpoints.');
            }
        } catch (QueryException) {
            abort(503, 'La table journal_audits est introuvable. Exécutez la migration avant d’utiliser ces endpoints.');
        }
    }
}
