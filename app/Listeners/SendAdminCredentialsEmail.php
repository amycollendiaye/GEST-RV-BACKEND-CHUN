<?php

namespace App\Listeners;

use App\Events\AdminCreated;
use App\Mail\AdminCredentialsMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAdminCredentialsEmail implements ShouldQueue
{
    public function handle(AdminCreated $event): void
    {
        $admin = $event->admin;
        $activationLink = rtrim(config('app.url'), '/') . '/api/activation?token=' . $admin->activation_token;

        Mail::to($admin->email)->send(new AdminCredentialsMail(
            $admin,
            $event->plainPassword,
            $activationLink
        ));
    }
}
