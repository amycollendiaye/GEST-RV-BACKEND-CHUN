<?php

namespace App\Services\DossierMedical;

use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;

class ConsulterDossierMedicalService
{
    public function __construct(
        private readonly DossierMedicalRepositoryInterface $dossierMedicalRepository
    ) {
    }

    public function executeById(string $id)
    {
        return $this->dossierMedicalRepository->findById($id);
    }

    public function executeByPatient(string $patientId)
    {
        return $this->dossierMedicalRepository->findByPatientId($patientId);
    }
}
