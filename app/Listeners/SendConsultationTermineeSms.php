<?php

namespace App\Listeners;

use App\Events\ConsultationTerminee;
use App\Services\RendezVous\NotifierRendezVousService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendConsultationTermineeSms implements ShouldQueue
{
    public function __construct(
        private readonly NotifierRendezVousService $notifierRendezVousService
    ) {
    }

    public function handle(ConsultationTerminee $event): void
    {
        $this->notifierRendezVousService->notifierConsultationTerminee(
            $event->consultation->loadMissing(['patient', 'medecin', 'rendezVous.serviceMedical']),
            $event->rendezVous->loadMissing(['patient', 'medecin', 'serviceMedical'])
        );
    }
}
