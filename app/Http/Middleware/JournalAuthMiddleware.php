<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Observers\AuthObserver;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JournalAuthMiddleware
{
    public function __construct(
        private readonly AuthObserver $authObserver
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userBeforeResponse = $request->user();
        $response = $next($request);

        if (!$response->isSuccessful()) {
            return $response;
        }

        if ($request->is('api/auth/login')) {
            $this->journaliserConnexion($request, $response);
        }

        if ($request->is('api/auth/logout')) {
            $this->journaliserDeconnexion($request, $userBeforeResponse);
        }

        return $response;
    }

    private function journaliserConnexion(Request $request, Response $response): void
    {
        if (!$response instanceof JsonResponse) {
            return;
        }

        $payload = $response->getData(true);
        $role = data_get($payload, 'data.user.role');
        $userId = data_get($payload, 'data.user.id');
        $login = data_get($payload, 'data.user.login', $request->input('login'));

        if (!$role || !$login) {
            return;
        }

        $auteur = $role === 'PATIENT' ? null : PersonelHopital::with('infosConnexion')->find($userId);

        $this->authObserver->connexion($auteur, [
            'login' => $login,
            'role' => $role,
            'adresse_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'date_heure' => now()->toDateTimeString(),
        ]);
    }

    private function journaliserDeconnexion(Request $request, mixed $user): void
    {
        if (!($user instanceof PersonelHopital) && !($user instanceof Patient)) {
            return;
        }

        $login = $user instanceof Patient ? $user->login : $user->infosConnexion?->login;
        $role = $user instanceof Patient ? 'PATIENT' : $user->role;

        $this->authObserver->deconnexion($user instanceof PersonelHopital ? $user : null, [
            'login' => $login,
            'role' => $role,
            'adresse_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'date_heure' => now()->toDateTimeString(),
        ]);
    }
}
