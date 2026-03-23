<?php

namespace App\Mail;

use App\Models\PersonelHopital;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCredentialsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly PersonelHopital $admin,
        public readonly string $plainPassword,
        public readonly string $activationLink
    ) {
    }

    public function build()
    {
        return $this->subject('Activation de votre compte administrateur')
            ->view('emails.admin-credentials')
            ->with([
                'admin' => $this->admin,
                'login' => $this->admin->infosConnexion?->login,
                'plainPassword' => $this->plainPassword,
                'activationLink' => $this->activationLink,
            ]);
    }
}
