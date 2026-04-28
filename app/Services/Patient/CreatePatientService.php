<?php

namespace App\Services\Patient;

use App\Events\PatientCreated;
use App\Models\Patient;
use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;
use App\Repositories\Interfaces\PatientRepositoryInterface;
use App\Services\DossierMedical\NumeroDossierGeneratorService;
use App\Services\MatriculeGeneratorService;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreatePatientService
{
    public function __construct(
        private readonly PatientRepositoryInterface $patientRepository,
        private readonly DossierMedicalRepositoryInterface $dossierMedicalRepository,
        private readonly MatriculeGeneratorService $matriculeGenerator,
        private readonly PasswordGeneratorService $passwordGenerator,
        private readonly NumeroDossierGeneratorService $numeroDossierGenerator
    ) {
    }

    public function execute(array $payload): array
    {
        // Génération du matricule et mot de passe
        $matricule = $this->matriculeGenerator->genererPatient();
        $plainPassword = $this->passwordGenerator->genererTemporaire();
        
        $payload['matricule'] = $matricule;
        $payload['login'] = $this->passwordGenerator->genererLoginPatientDepuisMatricule($matricule);
        $payload['password'] = Hash::make($plainPassword, ['rounds' => 12]);
        $payload['first_login'] = true;
        $payload['activation_token'] = (string) Str::uuid();
        $payload['activation_token_expires_at'] = now()->addHours(24);

        // Log des identifiants pour audit
        Log::info('Creation patient - Identifiants temporaire', [
            'matricule' => $matricule,
            'login' => $payload['login'],
            'password_temporaire' => $plainPassword,
        ]);

        // Note: Transaction supprimée car elle causait des problèmes avec PostgreSQL
        // quand les observers (comme PatientObserver) accèdent à auth()->user()
        Log::info('[DEBUG] Creation patient SANS transaction wrapper (transaction PostgreSQL causait des erreurs)');
        
        $patient = $this->patientRepository->create($payload);
        Log::info('[DEBUG] Patient cree, id: ' . $patient->id);

        $numeroDossier = $this->numeroDossierGenerator->generer();
        Log::info('[DEBUG] Numero dossier genere: ' . $numeroDossier);
        
        $this->dossierMedicalRepository->create([
            'numero_dossier' => $numeroDossier,
            'patient_id' => $patient->id,
        ]);
        Log::info('[DEBUG] Dossier medical cree');

        event(new PatientCreated($patient, $plainPassword));

        return [
            'patient' => $patient->fresh(['dossierMedical']),
            'credentials' => [
                'login' => $payload['login'],
                'password' => $plainPassword,
            ],
        ];
    }
}
