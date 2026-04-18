<?php

namespace App\Listeners;

use App\Events\PatientCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPatientCredentialsMail implements ShouldQueue
{
    public function handle(PatientCreated $event): void
    {
        $patient = $event->patient;
        
        // URL du frontend: utiliser FRONTEND_URL si défini, sinon utiliser localhost:8080 par défaut
        $frontendUrl = config('app.frontend_url', 'http://localhost:8080');
        $activationLink = rtrim($frontendUrl, '/') . '/activation?token=' . $patient->activation_token;

        $subject = 'Bienvenue - Vos identifiants de connexion';
        $body = <<<EOT
Bonjour {$patient->prenom} {$patient->nom},

Votre compte patient a été créé avec succès.

Voici vos identifiants de connexion:
- Login: {$patient->login}
- Mot de passe temporaire: {$event->plainPassword}

Pour activer votre compte, cliquez sur le lien ci-dessous:
{$activationLink}

Ce lien expire dans 24 heures.

Cordialement,
L'équipe EaseAppointHub
EOT;

        try {
            Mail::raw($body, function ($message) use ($patient, $subject) {
                $message->to($patient->email)
                    ->subject($subject);
            });
            
            Log::info('Email de création patient envoyé à: ' . $patient->email);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email patient: ' . $e->getMessage());
        }
    }
}
