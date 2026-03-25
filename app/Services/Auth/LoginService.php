<?php

namespace App\Services\Auth;

use App\Models\InfosConnexion;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function execute(string $login, string $password): array
    {
        $info = InfosConnexion::with('personnel')->where('login', $login)->first();

        if ($info) {
            if (!Hash::check($password, $info->password)) {
                abort(401, 'Identifiants invalides');
            }

            if ($info->first_login) {
                return [
                    'force_password_change' => true,
                ];
            }

            $token = $info->personnel->createToken('api')->plainTextToken;

            return [
                'token' => $token,
                'user' => $info->personnel,
                'force_password_change' => false,
            ];
        }

        $patient = Patient::where('login', $login)->first();
        if (!$patient || !Hash::check($password, $patient->password)) {
            abort(401, 'Identifiants invalides');
        }

        if ($patient->first_login) {
            return [
                'force_password_change' => true,
            ];
        }

        $token = $patient->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user' => $patient,
            'force_password_change' => false,
        ];
    }
}
