<?php

namespace App\Services\ServiceMedical;

use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;

class CreateServiceMedicalService
{
    public function __construct(
        private readonly ServiceMedicalRepositoryInterface $serviceMedicalRepository
    ) {
    }

    public function execute(array $data)
    {
        return $this->serviceMedicalRepository->create($data);
    }
}
