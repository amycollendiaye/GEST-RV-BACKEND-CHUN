<?php

namespace App\Services;

use App\Models\PersonelHopital;
use Illuminate\Support\Facades\DB;

class MatriculeGeneratorService
{
    public function genererMedecin(): string
    {
        return $this->generer('MED-FANN');
    }

    public function genererSecretaire(): string
    {
        return $this->generer('SEC-FANN');
    }

    public function genererAdmin(): string
    {
        return $this->generer('ADM-FANN');
    }

    private function generer(string $prefix): string
    {
        return DB::transaction(function () use ($prefix) {
            $year = now()->format('Y');
            $prefixWithYear = $prefix . '-' . $year . '-';

            $last = PersonelHopital::withTrashed()
                ->where('matricule', 'like', $prefixWithYear . '%')
                ->orderBy('matricule', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($last && preg_match('/(\d{4})$/', $last->matricule, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            return sprintf('%s%04d', $prefixWithYear, $nextNumber);
        });
    }
}
