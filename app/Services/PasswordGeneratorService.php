<?php

namespace App\Services;

use App\Models\InfosConnexion;
use App\Models\Patient;

class PasswordGeneratorService
{
    public function genererTemporaire(): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $specials = '@$!%*?&';

        $password = [];
        $password[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password[] = $digits[random_int(0, strlen($digits) - 1)];
        $password[] = $specials[random_int(0, strlen($specials) - 1)];

        $all = $uppercase . $lowercase . $digits . $specials;
        for ($i = 0; $i < 4; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }

    public function genererLoginDepuisMatricule(string $matricule): string
    {
        $base = strtolower(str_replace('-', '', $matricule));
        $login = $base;
        $suffix = 1;

        while (InfosConnexion::where('login', $login)->exists()) {
            $login = $base . $suffix;
            $suffix++;
        }

        return $login;
    }

    public function genererLoginPatientDepuisMatricule(string $matricule): string
    {
        $base = strtolower(str_replace('-', '', $matricule));
        $login = $base;
        $suffix = 1;

        while (Patient::where('login', $login)->exists()) {
            $login = $base . $suffix;
            $suffix++;
        }

        return $login;
    }
}
