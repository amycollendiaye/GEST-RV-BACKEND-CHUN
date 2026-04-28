<?php

namespace App\Services\RendezVous;

use App\Exceptions\AucunCreneauDisponibleException;
use App\Models\PlanningMedecin;
use App\Models\RendezVous;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use Illuminate\Support\Carbon;

class AttributionAutomatiqueRendezVousService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository,
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly VerifierConsultationPrecedenteService $verifierConsultationPrecedenteService
    ) {
    }

    public function execute(string $patientId, array $data): RendezVous
    {
        return $this->assignForPatient($patientId, $data);
    }

    public function assignForPatient(string $patientId, array $data): RendezVous
    {
        $serviceId = $data['service_medical_id'];

        $this->verifierConsultationPrecedenteService->execute($patientId, $serviceId);

        $planning = $this->planningRepository->findClosestAvailableForService($serviceId);

        if (!$planning) {
            throw new AucunCreneauDisponibleException();
        }

        $count = $planning->attributedRendezVous()->count();
        $dateRendezVous = $this->calculerDateHeureRendezVous($planning, $count);

        return $this->rendezVousRepository->create([
            'patient_id' => $patientId,
            'service_medical_id' => $serviceId,
            'medecin_id' => $planning->medecin_id,
            'planning_medecin_id' => $planning->id,
            'date_rendez_vous' => $dateRendezVous,
            'motif' => $data['motif'],
            'statut' => 'PLANIFIER',
            'audit_context' => $data['audit_context'] ?? null,
        ])->load(['patient', 'medecin', 'serviceMedical', 'planningMedecin']);
    }

    private function calculerDateHeureRendezVous(PlanningMedecin $planning, int $ordre): Carbon
    {
        $date = $planning->date->toDateString();
        $ouverture = Carbon::parse($date . ' ' . $this->normaliserHeure($planning->heure_ouverture));
        $fermeture = Carbon::parse($date . ' ' . $this->normaliserHeure($planning->heure_fermeture));
        $dureeTotale = max(1, $ouverture->diffInMinutes($fermeture));
        $dureeParPatient = max(1, (int) floor($dureeTotale / max(1, $planning->capacite)));

        return $ouverture->copy()->addMinutes($dureeParPatient * $ordre);
    }

    private function normaliserHeure(mixed $heure): string
    {
        if ($heure instanceof Carbon) {
            return $heure->format('H:i:s');
        }

        return Carbon::parse((string) $heure)->format('H:i:s');
    }
}
