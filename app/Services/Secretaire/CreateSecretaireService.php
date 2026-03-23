<?php

namespace App\Services\Secretaire;

use App\Events\SecretaireCreated;
use App\Repositories\Interfaces\SecretaireRepositoryInterface;
use App\Services\MatriculeGeneratorService;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSecretaireService
{
    public function __construct(
        private readonly SecretaireRepositoryInterface $secretaireRepository,
        private readonly MatriculeGeneratorService $matriculeGenerator,
        private readonly PasswordGeneratorService $passwordGenerator
    ) {
    }

    public function execute(array $data)
    {
        $matricule = $this->matriculeGenerator->genererSecretaire();
        $plainPassword = $this->passwordGenerator->genererTemporaire();
        $login = $this->passwordGenerator->genererLoginDepuisMatricule($matricule);

        $payload = array_merge($data, [
            'matricule' => $matricule,
            'role' => 'SECRETAIRE',
            'activation_token' => (string) Str::uuid(),
            'activation_token_expires_at' => now()->addHours(24),
            'service_medical_id' => $data['service_medical_id'] ?? $data['service_id'] ?? null,
        ]);

        unset($payload['service_id'], $payload['specialite']);

        $secretaire = $this->secretaireRepository->create($payload);

        $secretaire->infosConnexion()->create([
            'login' => $login,
            'password' => Hash::make($plainPassword, ['rounds' => 12]),
            'first_login' => true,
        ]);

        event(new SecretaireCreated($secretaire, $plainPassword));

        return $secretaire;
    }
}
