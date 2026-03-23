<?php

namespace App\Services\ServiceMedical;

use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;

class UpdateServiceMedicalService
{
    public function __construct(
        private readonly ServiceMedicalRepositoryInterface $serviceMedicalRepository
    ) {
    }

    public function execute(string $id, array $data)
    {
        return $this->serviceMedicalRepository->update($id, $data);
    }
}
