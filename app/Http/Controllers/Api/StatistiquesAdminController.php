<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonelHopital;
use App\Services\Statistiques\StatistiquesAdminService;
use Illuminate\Support\Facades\Gate;

class StatistiquesAdminController extends Controller
{
    public function __construct(
        private readonly StatistiquesAdminService $statistiquesAdminService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/statistiques/admin",
     *     tags={"Statistiques"},
     *     summary="Récupérer le tableau de bord administrateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques administrateur",
     *         @OA\JsonContent(ref="#/components/schemas/StatistiquesAdminResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function __invoke()
    {
        if (!(auth()->user() instanceof PersonelHopital)) {
            abort(403, 'Seul l\'administrateur peut accéder à ce tableau de bord.');
        }

        Gate::authorize('statistiques.admin');

        return response()->json([
            'success' => true,
            'message' => 'Statistiques administrateur',
            'data' => $this->statistiquesAdminService->getStatistiques(),
            'errors' => null,
        ]);
    }
}
