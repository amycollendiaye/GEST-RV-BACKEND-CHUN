<?php

namespace App\Services\Auth;

use App\Models\InfosConnexion;
use App\Models\Patient;
use App\Models\PersonelHopital;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function execute(string $login, string $currentPassword, string $newPassword)
    {
        $info = InfosConnexion::with('personnel')->where('login', $login)->first();

        if ($info) {
            if (!Hash::check($currentPassword, $info->password)) {
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

        $patient = Patient::where('login', $login)->first();
        if (!$patient || !Hash::check($currentPassword, $patient->password)) {
            abort(401, 'Identifiants invalides');
        }

        if (!$patient->first_login) {
            abort(403, 'Le mot de passe a déjà été changé');
        }

        $patient->update([
            'password' => Hash::make($newPassword, ['rounds' => 12]),
            'first_login' => false,
        ]);

        return $patient;
    }
}
