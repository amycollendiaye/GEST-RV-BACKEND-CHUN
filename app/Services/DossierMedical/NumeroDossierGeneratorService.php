<?php

namespace App\Services\DossierMedical;

use App\Models\DossierMedical;
use Illuminate\Support\Facades\DB;

class NumeroDossierGeneratorService
{
    public function generer(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $prefixWithYear = 'DOS-FANN-' . $year . '-';

            $last = DossierMedical::withTrashed()
                ->where('numero_dossier', 'like', $prefixWithYear . '%')
                ->orderBy('numero_dossier', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($last && preg_match('/(\d{4})$/', $last->numero_dossier, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            return sprintf('%s%04d', $prefixWithYear, $nextNumber);
        });
    }
}
