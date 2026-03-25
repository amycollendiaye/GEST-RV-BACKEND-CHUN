<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class LogoutService
{
    public function execute(Authenticatable $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
