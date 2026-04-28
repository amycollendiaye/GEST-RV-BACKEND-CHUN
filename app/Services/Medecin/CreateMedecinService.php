<?php

namespace App\Services\Medecin;

use App\Events\MedecinCreated;
use App\Repositories\Interfaces\MedecinRepositoryInterface;
use App\Services\MatriculeGeneratorService;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateMedecinService
{
    public function __construct(
        private readonly MedecinRepositoryInterface $medecinRepository,
        private readonly MatriculeGeneratorService $matriculeGenerator,
        private readonly PasswordGeneratorService $passwordGenerator
    ) {
    }

    public function execute(array $data): array
    {
        $matricule = $this->matriculeGenerator->genererMedecin();
        $plainPassword = $this->passwordGenerator->genererTemporaire();
        $login = $this->passwordGenerator->genererLoginDepuisMatricule($matricule);

        $payload = array_merge($data, [
            'matricule' => $matricule,
            'role' => 'MEDECIN',
            'activation_token' => (string) Str::uuid(),
            'activation_token_expires_at' => now()->addHours(24),
            'service_medical_id' => $data['service_medical_id'] ?? $data['service_id'] ?? null,
        ]);

        unset($payload['service_id']);

        $medecin = $this->medecinRepository->create($payload);

        $medecin->infosConnexion()->create([
            'login' => $login,
            'password' => Hash::make($plainPassword, ['rounds' => 12]),
            'first_login' => true,
        ]);

        try {
            event(new MedecinCreated($medecin, $plainPassword));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des identifiants au médecin : ' . $e->getMessage());
        }

        return [
            'medecin' => $medecin->load(['serviceMedical', 'infosConnexion']),
            'credentials' => [
                'login' => $login,
                'password' => $plainPassword,
            ],
        ];
    }
}
