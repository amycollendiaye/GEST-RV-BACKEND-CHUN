<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RendezVous\AnnulerRendezVousRequest;
use App\Http\Requests\RendezVous\StoreRendezVousRequest;
use App\Http\Resources\RendezVousCollection;
use App\Http\Resources\RendezVousResource;
use App\Http\Resources\ServiceMedicalCollection;
use App\Models\Patient;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;
use App\Services\RendezVous\AnnulerRendezVousService;
use App\Services\RendezVous\CreateRendezVousService;
use App\Services\RendezVous\ListeRendezVousPatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RendezVousController extends Controller
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly ServiceMedicalRepositoryInterface $serviceMedicalRepository,
        private readonly CreateRendezVousService $createRendezVousService,
        private readonly AnnulerRendezVousService $annulerRendezVousService,
        private readonly ListeRendezVousPatientService $listeRendezVousPatientService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/rendez-vous",
     *     tags={"RendezVous"},
     *     summary="Liste des rendez-vous",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="medecin_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousListResponse")
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
        Gate::authorize('rendezvous.viewAny');

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'medecin_id',
            'date_debut',
            'date_fin',
            'sort_by',
            'sort_dir',
        ]);

        $rendezVous = $this->rendezVousRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des rendez-vous',
            'data' => new RendezVousCollection($rendezVous),
            'errors' => null,
        ]);
    }

    // Conserved for backward compatibility in code, but documented in AttributionRendezVousController.
    public function store(StoreRendezVousRequest $request)
    {
        Gate::authorize('rendezvous.create');

        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        $rendezVous = $this->createRendezVousService->execute($user->id, $request->validated());

        $rendezVous->load(['patient', 'serviceMedical', 'medecin']);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous créé avec succès',
            'data' => new RendezVousResource($rendezVous),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/rendez-vous/{id}",
     *     tags={"RendezVous"},
     *     summary="Détail d'un rendez-vous",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousResponse")
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
        $rendezVous = $this->rendezVousRepository->findById($id);
        if (!$rendezVous) {
            abort(404, 'Rendez-vous introuvable');
        }

        Gate::authorize('rendezvous.view', $rendezVous);

        return response()->json([
            'success' => true,
            'message' => 'Détail du rendez-vous',
            'data' => new RendezVousResource($rendezVous),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/rendez-vous/mes-rendez-vous",
     *     tags={"RendezVous"},
     *     summary="Liste des rendez-vous du patient connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="medecin_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousListResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function mesRendezVous(Request $request)
    {
        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'medecin_id',
            'date_debut',
            'date_fin',
            'sort_by',
            'sort_dir',
        ]);

        $rendezVous = $this->listeRendezVousPatientService->execute($user->id, $filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste de mes rendez-vous',
            'data' => new RendezVousCollection($rendezVous),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/rendez-vous/{id}/annuler",
     *     tags={"RendezVous"},
     *     summary="Annuler un rendez-vous",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousResponse")
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Annulation impossible",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function annuler(AnnulerRendezVousRequest $request, string $id)
    {
        $rendezVous = $this->rendezVousRepository->findById($id);
        if (!$rendezVous) {
            abort(404, 'Rendez-vous introuvable');
        }

        Gate::authorize('rendezvous.annuler', $rendezVous);

        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        $rendezVous = $this->annulerRendezVousService->execute($rendezVous, $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous annulé avec succès',
            'data' => new RendezVousResource($rendezVous),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/rendez-vous/{id}/statut",
     *     tags={"RendezVous"},
     *     summary="Changer le statut d'un rendez-vous",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousStatutUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousResponse")
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
        $rendezVous = $this->rendezVousRepository->findById($id);
        if (!$rendezVous) {
            abort(404, 'Rendez-vous introuvable');
        }

        Gate::authorize('rendezvous.changerStatut', $rendezVous);

        $validated = $request->validate([
            'statut' => 'required|in:PLANIFIER,FAIT,ANNULER',
        ], [
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut est invalide.',
        ]);

        $rendezVous = $this->rendezVousRepository->update($rendezVous->id, [
            'statut' => $validated['statut'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du rendez-vous mis à jour',
            'data' => new RendezVousResource($rendezVous),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/services/disponibles",
     *     tags={"RendezVous"},
     *     summary="Liste des services médicaux disponibles",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="medecin_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceListResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function servicesDisponibles(Request $request)
    {
        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'medecin_id',
            'date_debut',
            'date_fin',
            'sort_by',
            'sort_dir',
        ]);
        $filters['etat'] = 'DISPONIBLE';

        $services = $this->serviceMedicalRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des services disponibles',
            'data' => new ServiceMedicalCollection($services),
            'errors' => null,
        ]);
    }
}
