<?php

namespace App\Repositories;

use App\Models\DossierMedical;
use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;

class DossierMedicalRepository implements DossierMedicalRepositoryInterface
{
    public function findById(string $id): ?DossierMedical
    {
        return DossierMedical::with([
            'patient',
            'patient.rendezVous',
            'consultations.rendezVous.serviceMedical',
            'consultations.medecin',
        ])->find($id);
    }

    public function findByPatientId(string $patientId): ?DossierMedical
    {
        return DossierMedical::with([
            'patient',
            'patient.rendezVous',
            'consultations.rendezVous.serviceMedical',
            'consultations.medecin',
        ])->where('patient_id', $patientId)->first();
    }

    public function create(array $data): DossierMedical
    {
        return DossierMedical::create($data);
    }

    public function update(string $id, array $data): DossierMedical
    {
        $dossier = $this->findById($id);

        if (!$dossier) {
            abort(404, 'Dossier médical introuvable');
        }

        $dossier->update($data);

        return $dossier->fresh();
    }
}
