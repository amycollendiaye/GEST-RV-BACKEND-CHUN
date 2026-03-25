<?php

namespace App\Repositories\Interfaces;

use App\Models\DossierMedical;

interface DossierMedicalRepositoryInterface
{
    public function findById(string $id): ?DossierMedical;

    public function findByPatientId(string $patientId): ?DossierMedical;

    public function create(array $data): DossierMedical;

    public function update(string $id, array $data): DossierMedical;
}
