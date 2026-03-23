<?php

namespace App\Services\ServiceMedical;

use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;

class DeleteServiceMedicalService
{
    public function __construct(
        private readonly ServiceMedicalRepositoryInterface $serviceMedicalRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->serviceMedicalRepository->delete($id);
    }
}
