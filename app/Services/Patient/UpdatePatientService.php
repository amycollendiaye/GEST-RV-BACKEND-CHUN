<?php

namespace App\Services\Patient;

use App\Repositories\Interfaces\PatientRepositoryInterface;

class UpdatePatientService
{
    public function __construct(
        private readonly PatientRepositoryInterface $patientRepository
    ) {
    }

    public function execute(string $id, array $data)
    {
        return $this->patientRepository->update($id, $data);
    }
}
