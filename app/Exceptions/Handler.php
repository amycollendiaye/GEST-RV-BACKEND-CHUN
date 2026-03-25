<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use App\Exceptions\RendezVousDejaExistantException;
use App\Exceptions\ConsultationNonTermineeException;
use App\Exceptions\AnnulationImpossibleException;
use App\Exceptions\DossierMedicalModificationInterditeException;
use App\Exceptions\PlanningDejaExistantException;
use App\Exceptions\PlanningNonModifiableException;
use App\Exceptions\AucunCreneauDisponibleException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($this->isApiRequest($request)) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'data' => null,
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifie',
                    'data' => null,
                    'errors' => [
                        'type' => 'authentication',
                        'detail' => 'Authentification requise pour acceder a cette ressource.',
                    ],
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acces refuse',
                    'data' => null,
                    'errors' => [
                        'type' => 'authorization',
                        'detail' => 'Vous n avez pas les droits pour acceder a cette ressource.',
                    ],
                ], 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ressource non trouvée',
                    'data' => null,
                    'errors' => [
                        'type' => 'not_found',
                        'detail' => 'La ressource demandee est introuvable.',
                    ],
                ], 404);
            }

            if ($e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $this->httpExceptionMessage($e),
                    'data' => null,
                    'errors' => $this->httpExceptionErrors($e),
                ], $e->getStatusCode());
            }

            if ($e instanceof RendezVousDejaExistantException
                || $e instanceof ConsultationNonTermineeException
                || $e instanceof AnnulationImpossibleException
                || $e instanceof DossierMedicalModificationInterditeException
                || $e instanceof PlanningDejaExistantException
                || $e instanceof PlanningNonModifiableException
                || $e instanceof AucunCreneauDisponibleException
            ) {
                $status = $e->getCode() ?: 400;
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => null,
                    'errors' => null,
                ], $status);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'data' => null,
                'errors' => [
                    'type' => 'server',
                    'detail' => 'Une erreur interne est survenue sur le serveur.',
                ],
            ], 500);
        }

        return parent::render($request, $e);
    }

    private function isApiRequest($request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function httpExceptionMessage(HttpException $e): string
    {
        $message = trim((string) $e->getMessage());

        if ($message !== '') {
            return $message;
        }

        return match ($e->getStatusCode()) {
            401 => 'Non authentifie',
            403 => 'Acces refuse',
            404 => 'Ressource non trouvée',
            500 => 'Erreur serveur',
            default => $e->getMessage() ?: 'Erreur HTTP',
        };
    }

    private function httpExceptionErrors(HttpException $e): ?array
    {
        $message = trim((string) $e->getMessage());

        return match ($e->getStatusCode()) {
            401 => [
                'type' => 'authentication',
                'detail' => $message !== '' ? $message : 'Authentification requise pour acceder a cette ressource.',
            ],
            403 => [
                'type' => 'authorization',
                'detail' => $message !== '' ? $message : 'Vous n avez pas les droits pour acceder a cette ressource.',
            ],
            404 => [
                'type' => 'not_found',
                'detail' => $message !== '' ? $message : 'La ressource demandee est introuvable.',
            ],
            500 => [
                'type' => 'server',
                'detail' => $message !== '' ? $message : 'Une erreur interne est survenue sur le serveur.',
            ],
            default => null,
        };
    }
}
