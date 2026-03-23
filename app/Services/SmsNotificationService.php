<?php

namespace App\Services;

use App\Services\Interfaces\SmsNotificationInterface;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsNotificationService implements SmsNotificationInterface
{
    public function envoyerCredsPersonnel(
        string $telephone,
        string $matricule,
        string $login,
        string $password,
        string $lienActivation
    ): void {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::warning('Configuration Twilio incomplète. SMS non envoyé.', [
                'telephone' => $telephone,
                'matricule' => $matricule,
                'login' => $login,
                'password_temporaire' => $password,
                'lien_activation' => $lienActivation,
            ]);
            return;
        }

        $client = new Client($sid, $token);

        $message = "Matricule: {$matricule}. Login: {$login}. "
            . "Mot de passe temporaire: {$password}. "
            . "Activation: {$lienActivation}";

        $client->messages->create($telephone, [
            'from' => $from,
            'body' => $message,
        ]);
    }
}
