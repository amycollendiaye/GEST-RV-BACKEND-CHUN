<?php

namespace App\Services\Activation;

use App\Models\PersonelHopital;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

class ActivationService
{
    public function validateToken(string $token)
    {
        $user = $this->findValidUser($token);
        if (!$user) {
            abort(404, 'Token invalide ou expiré');
        }

        return $user;
    }

    public function updatePassword(string $token, string $password): string
    {
        $user = $this->findValidUser($token);
        if (!$user) {
            abort(404, 'Token invalide ou expiré');
        }

        if ($user instanceof PersonelHopital) {
            $user->infosConnexion()->update([
                'password' => Hash::make($password, ['rounds' => 12]),
                'first_login' => false,
            ]);

            $user->update([
                'activation_token' => null,
                'activation_token_expires_at' => null,
            ]);

            return $user->createToken('api')->plainTextToken;
        }

        $user->update([
            'password' => Hash::make($password, ['rounds' => 12]),
            'first_login' => false,
            'activation_token' => null,
            'activation_token_expires_at' => null,
        ]);

        return $user->createToken('api')->plainTextToken;
    }

    private function findValidUser(string $token)
    {
        $personnel = PersonelHopital::where('activation_token', $token)
            ->where(function ($query) {
                $query->whereNull('activation_token_expires_at')
                    ->orWhere('activation_token_expires_at', '>', now());
            })->first();

        if ($personnel) {
            return $personnel;
        }

        return Patient::where('activation_token', $token)
            ->where(function ($query) {
                $query->whereNull('activation_token_expires_at')
                    ->orWhere('activation_token_expires_at', '>', now());
            })
            ->first();
    }
}
