<?php

namespace App\Services\Auth;

use App\Models\InfosConnexion;
use App\Models\PersonelHopital;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function execute(string $login, string $currentPassword, string $newPassword): PersonelHopital
    {
        $info = InfosConnexion::with('personnel')->where('login', $login)->first();

        if (!$info || !Hash::check($currentPassword, $info->password)) {
            abort(401, 'Identifiants invalides');
        }

        if (!$info->first_login) {
            abort(403, 'Le mot de passe a déjà été changé');
        }

        $info->update([
            'password' => Hash::make($newPassword, ['rounds' => 12]),
            'first_login' => false,
        ]);

        return $info->personnel;
    }
}
