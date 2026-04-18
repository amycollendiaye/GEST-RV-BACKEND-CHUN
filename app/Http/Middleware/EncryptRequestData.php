<?php
// app/Http/Middleware/EncryptRequestData.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EncryptionService;

class EncryptRequestData
{
    // Champs JAMAIS retournés dans les réponses API
    private array $hiddenFields = [
        'password',
        'password_confirmation',
    ];

    // Champs sensibles à déchiffrer avant envoi au client
    

    public function __construct(
        private EncryptionService $encryption
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // On masque les champs sensibles dans les logs
        $this->sanitizeRequestForLogs($request);

        return $response;
    }

    /**
     * Supprime les données sensibles des logs Laravel
     */
    private function sanitizeRequestForLogs(Request $request): void
    {
        $request->replace(
            collect($request->all())
                ->map(function ($value, $key) {
                    if (in_array($key, $this->hiddenFields)) {
                        return '***MASQUÉ***';
                    }
                    return $value;
                })
                ->toArray()
        );
    }
}