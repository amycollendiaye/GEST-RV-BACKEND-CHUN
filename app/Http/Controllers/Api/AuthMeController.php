<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Me\AdminMeResource;
use App\Http\Resources\Me\MedecinMeResource;
use App\Http\Resources\Me\PatientMeResource;
use App\Http\Resources\Me\SecretaireMeResource;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Services\Me\GetAdminMeService;
use App\Services\Me\GetMedecinMeService;
use App\Services\Me\GetPatientMeService;
use App\Services\Me\GetSecretaireMeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AuthMeController extends Controller
{
    public function __construct(
        private readonly GetAdminMeService $adminMeService,
        private readonly GetMedecinMeService $medecinMeService,
        private readonly GetSecretaireMeService $secretaireMeService,
        private readonly GetPatientMeService $patientMeService,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Auth"},
     *     summary="Profil de l'utilisateur connecté",
     *     description="Récupère le profil de l'utilisateur connecté en fonction de son rôle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(ref="#/components/schemas/AdminMeResponse"),
     *                 @OA\Schema(ref="#/components/schemas/MedecinMeResponse"),
     *                 @OA\Schema(ref="#/components/schemas/SecretaireMeResponse"),
     *                 @OA\Schema(ref="#/components/schemas/PatientMeResponse")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();

        if ($user instanceof Patient) {
            return $this->mePatient();
        }

        return match ($user?->role) {
            'ADMIN' => $this->meAdmin(),
            'MEDECIN' => $this->meMedecin(),
            'SECRETAIRE' => $this->meSecretaire(),
            default => response()->json([
                'success' => false,
                'message' => 'Role non reconnu',
                'data' => null,
                'errors' => null,
            ], 403),
        };
    }

    /**
     * @OA\Get(
     *     path="/auth/me/admin",
     *     tags={"Auth"},
     *     summary="Profil administrateur",
     *     description="Récupère le profil de l'administrateur connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AdminMeResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function meAdmin(): JsonResponse
    {
        Gate::authorize('me.viewAdmin');

        /** @var PersonelHopital $user */
        $user = auth()->user();
        $data = $this->adminMeService->execute($user);

        return (new AdminMeResource($data))->response();
    }

    /**
     * @OA\Get(
     *     path="/auth/me/medecin",
     *     tags={"Auth"},
     *     summary="Profil médecin",
     *     description="Récupère le profil du médecin connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinMeResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function meMedecin(): JsonResponse
    {
        Gate::authorize('me.viewMedecin');

        /** @var PersonelHopital $user */
        $user = auth()->user();
        $data = $this->medecinMeService->execute($user);

        return (new MedecinMeResource($data))->response();
    }

    /**
     * @OA\Get(
     *     path="/auth/me/secretaire",
     *     tags={"Auth"},
     *     summary="Profil secrétaire",
     *     description="Récupère le profil du secrétaire connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireMeResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function meSecretaire(): JsonResponse
    {
        Gate::authorize('me.viewSecretaire');

        /** @var PersonelHopital $user */
        $user = auth()->user();
        $data = $this->secretaireMeService->execute($user);

        return (new SecretaireMeResource($data))->response();
    }

    /**
     * @OA\Get(
     *     path="/auth/me/patient",
     *     tags={"Auth"},
     *     summary="Profil patient",
     *     description="Récupère le profil du patient connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PatientMeResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function mePatient(): JsonResponse
    {
        Gate::authorize('me.viewPatient');

        /** @var Patient $user */
        $user = auth()->user();
        $data = $this->patientMeService->execute($user);

        return (new PatientMeResource($data))->response();
    }
}
