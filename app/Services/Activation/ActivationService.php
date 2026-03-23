<?php

namespace App\Services\Activation;

use App\Models\PersonelHopital;
use Illuminate\Support\Facades\Hash;

class ActivationService
{
    public function validateToken(string $token): PersonelHopital
    {
        $personnel = $this->findValidPersonnel($token);
        if (!$personnel) {
            abort(404, 'Token invalide ou expiré');
        }

        return $personnel;
    }

    public function updatePassword(string $token, string $password): string
    {
        $personnel = $this->findValidPersonnel($token);
        if (!$personnel) {
            abort(404, 'Token invalide ou expiré');
        }

        $personnel->infosConnexion()->update([
            'password' => Hash::make($password, ['rounds' => 12]),
            'first_login' => false,
        ]);

        $personnel->update([
            'activation_token' => null,
            'activation_token_expires_at' => null,
        ]);

        return $personnel->createToken('api')->plainTextToken;
    }

    private function findValidPersonnel(string $token): ?PersonelHopital
    {
        return PersonelHopital::where('activation_token', $token)
            ->where(function ($query) {
                $query->whereNull('activation_token_expires_at')
                    ->orWhere('activation_token_expires_at', '>', now());
            })
            ->first();
    }
}
