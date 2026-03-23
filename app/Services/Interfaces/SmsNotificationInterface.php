<?php

namespace App\Services\Interfaces;

interface SmsNotificationInterface
{
    public function envoyerCredsPersonnel(
        string $telephone,
        string $matricule,
        string $login,
        string $password,
        string $lienActivation
    ): void;
}
