<?php

namespace App\Listeners;

use App\Events\RendezVousReprogramme;
use App\Services\RendezVous\NotifierRendezVousService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRendezVousReprogrammeSms implements ShouldQueue
{
    public function __construct(
        private readonly NotifierRendezVousService $notifierRendezVousService
    ) {
    }

    public function handle(RendezVousReprogramme $event): void
    {
        $this->notifierRendezVousService->notifierReprogrammation(
            $event->ancienRendezVous->loadMissing(['patient', 'medecin', 'serviceMedical']),
            $event->nouveauRendezVous->loadMissing(['patient', 'medecin', 'serviceMedical'])
        );
    }
}
