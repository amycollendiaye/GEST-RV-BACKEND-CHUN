<?php

namespace App\Services\Patient;

use App\Repositories\Interfaces\PatientRepositoryInterface;

class DeletePatientService
{
    public function __construct(
        private readonly PatientRepositoryInterface $patientRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->patientRepository->delete($id);
    }
}
