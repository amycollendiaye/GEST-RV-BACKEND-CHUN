<?php

namespace App\Listeners;

use App\Events\SecretaireCreated;
use App\Services\Interfaces\SmsNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSecretaireCredentialsSms implements ShouldQueue
{
    public function __construct(private readonly SmsNotificationInterface $smsService)
    {
    }

    public function handle(SecretaireCreated $event): void
    {
        $secretaire = $event->secretaire;
        $activationLink = rtrim(config('app.url'), '/') . '/api/activation?token=' . $secretaire->activation_token;

        $this->smsService->envoyerCredsPersonnel(
            $secretaire->telephone,
            $secretaire->matricule,
            $secretaire->infosConnexion?->login ?? '',
            $event->plainPassword,
            $activationLink
        );
    }
}
