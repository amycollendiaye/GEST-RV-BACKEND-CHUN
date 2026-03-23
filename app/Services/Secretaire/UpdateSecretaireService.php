<?php

namespace App\Services\Secretaire;

use App\Repositories\Interfaces\SecretaireRepositoryInterface;

class UpdateSecretaireService
{
    public function __construct(
        private readonly SecretaireRepositoryInterface $secretaireRepository
    ) {
    }

    public function execute(string $id, array $data)
    {
        $payload = $data;
        if (array_key_exists('service_medical_id', $data)) {
            $payload['service_medical_id'] = $data['service_medical_id'];
            unset($payload['service_medical_id']);
        } elseif (array_key_exists('service_id', $data)) {
            $payload['service_medical_id'] = $data['service_id'];
            unset($payload['service_id']);
        }

        unset($payload['role'], $payload['matricule'], $payload['login'], $payload['password'], $payload['specialite']);

        return $this->secretaireRepository->update($id, $payload);
    }
}
