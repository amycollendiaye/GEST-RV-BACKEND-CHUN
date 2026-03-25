<?php

namespace App\Listeners;

use App\Events\RendezVousAttribue;
use App\Services\RendezVous\NotifierRendezVousService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRendezVousAttribueSms implements ShouldQueue
{
    public function __construct(
        private readonly NotifierRendezVousService $notifierRendezVousService
    ) {
    }

    public function handle(RendezVousAttribue $event): void
    {
        $this->notifierRendezVousService->notifierAttribution($event->rendezVous->loadMissing([
            'patient',
            'medecin',
            'serviceMedical',
        ]));
    }
}
