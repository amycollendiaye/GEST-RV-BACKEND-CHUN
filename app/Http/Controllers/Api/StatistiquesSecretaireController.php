<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonelHopital;
use App\Services\Statistiques\StatistiquesSecretaireService;
use Illuminate\Support\Facades\Gate;

class StatistiquesSecretaireController extends Controller
{
    public function __construct(
        private readonly StatistiquesSecretaireService $statistiquesSecretaireService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/statistiques/secretaire",
     *     tags={"Statistiques"},
     *     summary="Récupérer le tableau de bord secrétaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques secrétaire",
     *         @OA\JsonContent(ref="#/components/schemas/StatistiquesSecretaireResponse")
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

        Gate::authorize('statistiques.secretaire');

        return response()->json([
            'success' => true,
            'message' => 'Statistiques secrétaire',
            'data' => $this->statistiquesSecretaireService->getStatistiques($user),
            'errors' => null,
        ]);
    }
}
