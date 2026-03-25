<?php

namespace App\Services\RendezVous;

use App\Models\Consultation;
use App\Models\RendezVous;
use App\Services\Interfaces\SmsNotificationInterface;

class NotifierRendezVousService
{
    public function __construct(
        private readonly SmsNotificationInterface $smsNotificationService
    ) {
    }

    public function notifierAttribution(RendezVous $rendezVous): void
    {
        $message = sprintf(
            'Votre rendez-vous au service %s est planifie le %s a %s avec le Dr %s %s. Motif: %s.',
            $rendezVous->serviceMedical?->nom ?? 'N/A',
            $rendezVous->date_rendez_vous?->format('d/m/Y'),
            $rendezVous->date_rendez_vous?->format('H:i'),
            $rendezVous->medecin?->prenom ?? '',
            $rendezVous->medecin?->nom ?? '',
            $rendezVous->motif
        );

        $this->send($rendezVous, $message);
    }

    public function notifierReprogrammation(RendezVous $ancienRendezVous, RendezVous $nouveauRendezVous): void
    {
        $message = sprintf(
            'Votre rendez-vous de suivi a ete reprogramme au service %s pour le %s a %s avec le Dr %s %s. Motif: %s.',
            $nouveauRendezVous->serviceMedical?->nom ?? 'N/A',
            $nouveauRendezVous->date_rendez_vous?->format('d/m/Y'),
            $nouveauRendezVous->date_rendez_vous?->format('H:i'),
            $nouveauRendezVous->medecin?->prenom ?? '',
            $nouveauRendezVous->medecin?->nom ?? '',
            $nouveauRendezVous->motif
        );

        $this->send($ancienRendezVous, $message);
    }

    public function notifierConsultationTerminee(Consultation $consultation, RendezVous $rendezVous): void
    {
        $message = sprintf(
            'Votre consultation du %s au service %s est terminee. Merci de votre passage.',
            $consultation->date_heure?->format('d/m/Y H:i'),
            $rendezVous->serviceMedical?->nom ?? 'N/A'
        );

        $this->send($rendezVous, $message);
    }

    private function send(RendezVous $rendezVous, string $message): void
    {
        $telephone = $rendezVous->patient?->telephone;

        if (!$telephone) {
            return;
        }

        $this->smsNotificationService->envoyerMessage($telephone, $message);
    }
}
