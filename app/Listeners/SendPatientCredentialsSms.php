<?php

namespace App\Listeners;

use App\Events\PatientCreated;
use App\Services\Interfaces\SmsNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPatientCredentialsSms implements ShouldQueue
{
    public function __construct(private readonly SmsNotificationInterface $smsService)
    {
    }

    public function handle(PatientCreated $event): void
    {
        $patient = $event->patient;
        $activationLink = rtrim(config('app.url'), '/') . '/api/activation?token=' . $patient->activation_token;

        $this->smsService->envoyerCredsPersonnel(
            $patient->telephone,
            $patient->matricule,
            $patient->login,
            $event->plainPassword,
            $activationLink
        );
    }
}
