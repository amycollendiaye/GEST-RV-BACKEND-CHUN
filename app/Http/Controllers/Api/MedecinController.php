<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medecin\StoreMedecinRequest;
use App\Http\Requests\Medecin\UpdateMedecinRequest;
use App\Http\Resources\MedecinCollection;
use App\Http\Resources\MedecinResource;
use App\Repositories\Interfaces\MedecinRepositoryInterface;
use App\Services\Medecin\CreateMedecinService;
use App\Services\Medecin\ChangerStatutMedecinService;
use App\Services\Medecin\DeleteMedecinService;
use App\Services\Medecin\UpdateMedecinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MedecinController extends Controller
{
    public function __construct(
        private readonly MedecinRepositoryInterface $medecinRepository,
        private readonly CreateMedecinService $createMedecinService,
        private readonly UpdateMedecinService $updateMedecinService,
        private readonly DeleteMedecinService $deleteMedecinService,
        private readonly ChangerStatutMedecinService $changerStatutMedecinService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/medecins",
     *     tags={"Medecins"},
     *     summary="Liste des médecins",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="specialite", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinListResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\PersonelHopital::class);

        // Forçage temporaire pour diagnostic
        $perPage = 6;
        
        \Illuminate\Support\Facades\Log::info('Appel index médecins', [
            'request_params' => $request->all(),
            'per_page_final' => $perPage
        ]);

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'specialite',
            'sort_by',
            'sort_dir',
        ]);

        $medecins = $this->medecinRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des médecins',
            'data' => new MedecinCollection($medecins),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/medecins",
     *     tags={"Medecins"},
     *     summary="Créer un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MedecinCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Créé",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(StoreMedecinRequest $request)
    {
        Gate::authorize('create', \App\Models\PersonelHopital::class);

        $medecin = $this->createMedecinService->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Médecin créé avec succès',
            'data' => new MedecinResource($medecin),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/medecins/{id}",
     *     tags={"Medecins"},
     *     summary="Afficher un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $id)
    {
        $medecin = $this->medecinRepository->findById($id);
        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        Gate::authorize('medecin.view', $medecin);

        return response()->json([
            'success' => true,
            'message' => 'Détail du médecin',
            'data' => new MedecinResource($medecin),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/medecins/{id}",
     *     tags={"Medecins"},
     *     summary="Mettre à jour un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MedecinUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateMedecinRequest $request, string $id)
    {
        $medecin = $this->medecinRepository->findById($id);
        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        Gate::authorize('medecin.update', $medecin);

        $medecin = $this->updateMedecinService->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Médecin mis à jour',
            'data' => new MedecinResource($medecin),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/medecins/{id}",
     *     tags={"Medecins"},
     *     summary="Supprimer un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $medecin = $this->medecinRepository->findById($id);
        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        Gate::authorize('medecin.delete', $medecin);

        $this->deleteMedecinService->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Médecin supprimé',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/medecins/{id}/statut",
     *     tags={"Medecins"},
     *     summary="Changer le statut d'un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StatutUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MedecinResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function changerStatut(Request $request, string $id)
    {
        $medecin = $this->medecinRepository->findById($id);
        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        Gate::authorize('medecin.changerStatut', $medecin);

        $validated = $request->validate([
            'statut' => 'required|in:ACTIF,INACTIF,ENCONGE',
        ]);

        $medecin = $this->changerStatutMedecinService->execute($id, $validated['statut']);

        return response()->json([
            'success' => true,
            'message' => 'Statut du médecin mis à jour',
            'data' => new MedecinResource($medecin),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/medecins/{id}/planning",
     *     tags={"Medecins"},
     *     summary="Planning d'un médecin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PlanningResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function planning(string $id)
    {
        $medecin = $this->medecinRepository->findById($id);
        if (!$medecin) {
            abort(404, 'Médecin introuvable');
        }

        Gate::authorize('medecin.view', $medecin);

        return response()->json([
            'success' => true,
            'message' => 'Planning du médecin',
            'data' => $medecin->planningMedecins,
            'errors' => null,
        ]);
    }
}
