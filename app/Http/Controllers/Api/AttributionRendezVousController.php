<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RendezVous\AttributionRendezVousRequest;
use App\Http\Resources\RendezVousResource;
use App\Models\Patient;
use App\Services\RendezVous\AttributionAutomatiqueRendezVousService;
use Illuminate\Support\Facades\Gate;

class AttributionRendezVousController extends Controller
{
    public function __construct(
        private readonly AttributionAutomatiqueRendezVousService $attributionAutomatiqueRendezVousService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/rendez-vous",
     *     tags={"RendezVous"},
     *     summary="Creer un rendez-vous avec attribution automatique",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cree",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Aucun creneau disponible", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=409, description="Conflit metier", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(AttributionRendezVousRequest $request)
    {
        Gate::authorize('rendezvous.create');

        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Acces refuse');
        }

        $rendezVous = $this->attributionAutomatiqueRendezVousService->execute($user->id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous cree avec succes',
            'data' => new RendezVousResource($rendezVous),
            'errors' => null,
        ], 201);
    }
}
