<?php

namespace App\Services\Patient;

use App\Events\PatientCreated;
use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;
use App\Repositories\Interfaces\PatientRepositoryInterface;
use App\Services\DossierMedical\NumeroDossierGeneratorService;
use App\Services\MatriculeGeneratorService;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function execute(array $data)
    {
        $matricule = $this->matriculeGenerator->genererPatient();
        $plainPassword = $this->passwordGenerator->genererTemporaire();
        $login = $this->passwordGenerator->genererLoginPatientDepuisMatricule($matricule);

        $payload = array_merge($data, [
            'matricule' => $matricule,
            'login' => $login,
            'password' => Hash::make($plainPassword, ['rounds' => 12]),
            'first_login' => true,
            'activation_token' => (string) Str::uuid(),
            'activation_token_expires_at' => now()->addHours(24),
        ]);

        $patient = DB::transaction(function () use ($payload) {
            $patient = $this->patientRepository->create($payload);

            $numeroDossier = $this->numeroDossierGenerator->generer();
            $this->dossierMedicalRepository->create([
                'numero_dossier' => $numeroDossier,
                'patient_id' => $patient->id,
            ]);

            return $patient;
        });

        event(new PatientCreated($patient, $plainPassword));

        return $patient->fresh(['dossierMedical']);
    }
}
