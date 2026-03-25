<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonelHopital;
use App\Services\Statistiques\StatistiquesMedecinService;
use Illuminate\Support\Facades\Gate;

class StatistiquesMedecinController extends Controller
{
    public function __construct(
        private readonly StatistiquesMedecinService $statistiquesMedecinService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/statistiques/medecin",
     *     tags={"Statistiques"},
     *     summary="Récupérer le tableau de bord médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques médecin",
     *         @OA\JsonContent(ref="#/components/schemas/StatistiquesMedecinResponse")
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
        $user = auth()->user();
        if (!($user instanceof PersonelHopital)) {
            abort(403, 'Accès refusé.');
        }

        Gate::authorize('statistiques.medecin');

        return response()->json([
            'success' => true,
            'message' => 'Statistiques médecin',
            'data' => $this->statistiquesMedecinService->getStatistiques($user),
            'errors' => null,
        ]);
    }
}
