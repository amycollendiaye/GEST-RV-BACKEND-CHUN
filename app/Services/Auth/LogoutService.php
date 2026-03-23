<?php

namespace App\Services\Auth;

use App\Models\PersonelHopital;

class LogoutService
{
    public function execute(PersonelHopital $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
