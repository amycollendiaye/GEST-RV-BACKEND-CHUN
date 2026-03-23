<?php

namespace App\Services\Admin;

use App\Events\AdminCreated;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Services\MatriculeGeneratorService;
use App\Services\PasswordGeneratorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminService
{
    public function __construct(
        private readonly AdminRepositoryInterface $adminRepository,
        private readonly MatriculeGeneratorService $matriculeGenerator,
        private readonly PasswordGeneratorService $passwordGenerator
    ) {
    }

    public function execute(array $data)
    {
        if ($this->adminRepository->adminExists()) {
            abort(403, 'Un administrateur existe déjà');
        }

        $matricule = $this->matriculeGenerator->genererAdmin();
        $plainPassword = $this->passwordGenerator->genererTemporaire();
        $login = $this->passwordGenerator->genererLoginDepuisMatricule($matricule);

        $payload = array_merge($data, [
            'matricule' => $matricule,
            'role' => 'ADMIN',
            'statut' => 'ACTIF',
            'activation_token' => (string) Str::uuid(),
            'activation_token_expires_at' => now()->addHours(24),
        ]);

        $admin = $this->adminRepository->create($payload);

        $admin->infosConnexion()->create([
            'login' => $login,
            'password' => Hash::make($plainPassword, ['rounds' => 12]),
            'first_login' => true,
        ]);

        event(new AdminCreated($admin, $plainPassword));

        return $admin;
    }
}
