<?php

namespace App\Listeners;

use App\Events\MedecinCreated;
use App\Services\Interfaces\SmsNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMedecinCredentialsSms implements ShouldQueue
{
    public function __construct(private readonly SmsNotificationInterface $smsService)
    {
    }

    public function handle(MedecinCreated $event): void
    {
        $medecin = $event->medecin;
        $activationLink = rtrim(config('app.url'), '/') . '/api/activation?token=' . $medecin->activation_token;

        $this->smsService->envoyerCredsPersonnel(
            $medecin->telephone,
            $medecin->matricule,
            $medecin->infosConnexion?->login ?? '',
            $event->plainPassword,
            $activationLink
        );
    }
}
