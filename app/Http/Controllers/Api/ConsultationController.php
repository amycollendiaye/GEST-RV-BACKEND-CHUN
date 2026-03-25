<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreConsultationRequest;
use App\Http\Requests\Consultation\UpdateConsultationRequest;
use App\Http\Requests\RendezVous\ReprogrammerRendezVousRequest;
use App\Http\Resources\ConsultationCollection;
use App\Http\Resources\ConsultationResource;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use App\Services\Consultation\CreateConsultationService;
use App\Services\Consultation\UpdateConsultationService;
use App\Services\RendezVous\CloturerConsultationService;
use App\Services\RendezVous\ReprogrammerRendezVousService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationRepositoryInterface $consultationRepository,
        private readonly CreateConsultationService $createConsultationService,
        private readonly UpdateConsultationService $updateConsultationService,
        private readonly CloturerConsultationService $cloturerConsultationService,
        private readonly ReprogrammerRendezVousService $reprogrammerRendezVousService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/consultations",
     *     tags={"Consultations"},
     *     summary="Liste des consultations",
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
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationListResponse")
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
        Gate::authorize('consultation.viewAny');

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

        $consultations = $this->consultationRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des consultations',
            'data' => new ConsultationCollection($consultations),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/consultations",
     *     tags={"Consultations"},
     *     summary="Créer une consultation",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Créé",
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationResponse")
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
    public function store(StoreConsultationRequest $request)
    {
        Gate::authorize('consultation.create');

        $user = auth()->user();
        $consultation = $this->createConsultationService->execute($request->validated(), $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Consultation enregistrée avec succès',
            'data' => new ConsultationResource($consultation),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/consultations/{id}",
     *     tags={"Consultations"},
     *     summary="Détail d'une consultation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationResponse")
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
        $consultation = $this->consultationRepository->findById($id);
        if (!$consultation) {
            abort(404, 'Consultation introuvable');
        }

        Gate::authorize('consultation.view', $consultation);

        return response()->json([
            'success' => true,
            'message' => 'Détail de la consultation',
            'data' => new ConsultationResource($consultation),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/consultations/{id}",
     *     tags={"Consultations"},
     *     summary="Mettre à jour une consultation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationResponse")
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateConsultationRequest $request, string $id)
    {
        $consultation = $this->consultationRepository->findById($id);
        if (!$consultation) {
            abort(404, 'Consultation introuvable');
        }

        Gate::authorize('consultation.update', $consultation);

        $consultation = $this->updateConsultationService->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Consultation mise à jour',
            'data' => new ConsultationResource($consultation),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/consultations/{id}/cloturer",
     *     tags={"Consultations"},
     *     summary="Cloturer une consultation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationClotureRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function cloturer(Request $request, string $id)
    {
        $consultation = $this->consultationRepository->findById($id);
        if (!$consultation) {
            abort(404, 'Consultation introuvable');
        }

        Gate::authorize('consultation.cloturer', $consultation);

        $validated = $request->validate([
            'mise_a_jour_dossier' => ['sometimes', 'array'],
            'mise_a_jour_dossier.maladies_chroniques' => ['sometimes', 'string', 'max:1000'],
            'mise_a_jour_dossier.traitements_en_cours' => ['sometimes', 'string', 'max:1000'],
        ]);

        $consultation = $this->cloturerConsultationService->execute(
            $consultation,
            $validated['mise_a_jour_dossier'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Consultation cloturee avec succes',
            'data' => new ConsultationResource($consultation),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/consultations/{id}/reprogrammer",
     *     tags={"Consultations"},
     *     summary="Reprogrammer un rendez-vous de suivi",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RendezVousReprogrammationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ConsultationReprogrammationResponse")
     *     ),
     *     @OA\Response(response=401, description="Non authentifie", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Acces refuse", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Introuvable", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function reprogrammer(ReprogrammerRendezVousRequest $request, string $id)
    {
        $consultation = $this->consultationRepository->findById($id);
        if (!$consultation) {
            abort(404, 'Consultation introuvable');
        }

        Gate::authorize('consultation.reprogrammer', $consultation);

        $rendezVous = $this->reprogrammerRendezVousService->execute(
            $consultation,
            $request->validated()['motif_suivi']
        );

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous de suivi reprogramme avec succes',
            'data' => [
                'consultation' => new ConsultationResource($consultation->fresh(['patient', 'medecin', 'rendezVous.serviceMedical'])),
                'nouveau_rendez_vous' => new \App\Http\Resources\RendezVousResource($rendezVous),
            ],
            'errors' => null,
        ]);
    }
}
