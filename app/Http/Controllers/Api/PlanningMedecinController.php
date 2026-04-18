<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanningMedecin\StorePlanningRequest;
use App\Http\Requests\PlanningMedecin\UpdatePlanningRequest;
use App\Http\Resources\PlanningMedecinCollection;
use App\Http\Resources\PlanningMedecinResource;
use App\Http\Resources\RendezVousCollection;
use App\Models\PersonelHopital;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;
use App\Services\PlanningMedecin\CreatePlanningService;
use App\Services\PlanningMedecin\DeletePlanningService;
use App\Services\PlanningMedecin\UpdatePlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanningMedecinController extends Controller
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository,
        private readonly CreatePlanningService $createPlanningService,
        private readonly UpdatePlanningService $updatePlanningService,
        private readonly DeletePlanningService $deletePlanningService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/plannings",
     *     tags={"Plannings"},
     *     summary="Creer un planning medecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cree",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=409, description="Planning deja existant", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(StorePlanningRequest $request)
    {
        Gate::authorize('planning.create');

        $user = auth()->user();
        if (!$user instanceof PersonelHopital || !in_array($user->role, ['ADMIN', 'MEDECIN'], true)) {
            abort(403, 'Acces refuse');
        }

        $validated = $request->validated();

        $medecin = $user;
        if ($user->role === 'ADMIN') {
            $medecin = PersonelHopital::query()
                ->where('role', 'MEDECIN')
                ->find($validated['medecin_id'] ?? null);

            if (!$medecin instanceof PersonelHopital) {
                abort(422, 'Le medecin selectionne est introuvable.');
            }
        }

        unset($validated['medecin_id']);

        $planning = $this->createPlanningService->execute($medecin, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Planning cree avec succes',
            'data' => new PlanningMedecinResource($planning),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/plannings/mes-plannings",
     *     tags={"Plannings"},
     *     summary="Lister les plannings du medecin connecte",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="medecin_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="disponible", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinListResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function mesPlannings(Request $request)
    {
        $user = auth()->user();
        if (!$user instanceof PersonelHopital || $user->role !== 'MEDECIN') {
            abort(403, 'Acces refuse');
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $filters = $request->only([
            'date_debut',
            'date_fin',
            'service_id',
            'medecin_id',
            'statut',
            'disponible',
            'sort_by',
            'sort_dir',
        ]);

        $plannings = $this->planningRepository->paginateForMedecin($user->id, $filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste de mes plannings',
            'data' => new PlanningMedecinCollection($plannings),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/plannings",
     *     tags={"Plannings"},
     *     summary="Lister tous les plannings",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="medecin_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="disponible", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinListResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(Request $request)
    {
        Gate::authorize('planning.viewAny');

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $filters = $request->only([
            'date_debut',
            'date_fin',
            'service_id',
            'medecin_id',
            'statut',
            'disponible',
            'sort_by',
            'sort_dir',
        ]);

        $plannings = $this->planningRepository->paginateAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des plannings',
            'data' => new PlanningMedecinCollection($plannings),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/plannings/{id}",
     *     tags={"Plannings"},
     *     summary="Detail d un planning",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $id)
    {
        $planning = $this->planningRepository->findById($id);
        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        Gate::authorize('planning.view', $planning);

        return response()->json([
            'success' => true,
            'message' => 'Detail du planning',
            'data' => new PlanningMedecinResource($planning),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/plannings/{id}",
     *     tags={"Plannings"},
     *     summary="Modifier un planning",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningMedecinResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=409, description="Conflit metier", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdatePlanningRequest $request, string $id)
    {
        $planning = $this->planningRepository->findById($id);
        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        Gate::authorize('planning.update', $planning);

        $planning = $this->updatePlanningService->execute($planning, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Planning mis a jour',
            'data' => new PlanningMedecinResource($planning),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/plannings/{id}",
     *     tags={"Plannings"},
     *     summary="Supprimer un planning",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Planning non modifiable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(string $id)
    {
        $planning = $this->planningRepository->findById($id);
        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        Gate::authorize('planning.delete', $planning);

        $this->deletePlanningService->execute($planning);

        return response()->json([
            'success' => true,
            'message' => 'Planning supprime avec succes',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/plannings/{id}/rendez-vous",
     *     tags={"Plannings"},
     *     summary="Lister les rendez-vous d un planning",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousListResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function rendezVous(Request $request, string $id)
    {
        $planning = $this->planningRepository->findById($id);
        if (!$planning) {
            abort(404, 'Planning introuvable');
        }

        $user = auth()->user();
        if (!$user instanceof PersonelHopital || !in_array($user->role, ['SECRETAIRE', 'MEDECIN'], true)) {
            abort(403, 'Acces refuse');
        }
        if ($user->role === 'MEDECIN' && $planning->medecin_id !== $user->id) {
            abort(403, 'Acces refuse');
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $rendezVous = $this->planningRepository->paginateRendezVous($id, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des rendez-vous du planning',
            'data' => new RendezVousCollection($rendezVous),
            'errors' => null,
        ]);
    }
}
