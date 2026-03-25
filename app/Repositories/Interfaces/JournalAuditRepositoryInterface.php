<?php

namespace App\Repositories\Interfaces;

use App\Models\JournalAudit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

interface JournalAuditRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?JournalAudit;

    public function create(array $data): JournalAudit;

    public function cursor(array $filters): LazyCollection;
}
