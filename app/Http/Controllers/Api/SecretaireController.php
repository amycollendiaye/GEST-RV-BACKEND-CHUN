<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaire\StoreSecretaireRequest;
use App\Http\Requests\Secretaire\UpdateSecretaireRequest;
use App\Http\Resources\SecretaireCollection;
use App\Http\Resources\SecretaireResource;
use App\Repositories\Interfaces\SecretaireRepositoryInterface;
use App\Services\Secretaire\CreateSecretaireService;
use App\Services\Secretaire\ChangerStatutSecretaireService;
use App\Services\Secretaire\DeleteSecretaireService;
use App\Services\Secretaire\UpdateSecretaireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SecretaireController extends Controller
{
    public function __construct(
        private readonly SecretaireRepositoryInterface $secretaireRepository,
        private readonly CreateSecretaireService $createSecretaireService,
        private readonly UpdateSecretaireService $updateSecretaireService,
        private readonly DeleteSecretaireService $deleteSecretaireService,
        private readonly ChangerStatutSecretaireService $changerStatutSecretaireService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/secretaires",
     *     tags={"Secretaires"},
     *     summary="Liste des secrétaires",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireListResponse")
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
        Gate::authorize('secretaire.viewAny');

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'sort_by',
            'sort_dir',
        ]);

        $secretaires = $this->secretaireRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des secrétaires',
            'data' => new SecretaireCollection($secretaires),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/secretaires",
     *     tags={"Secretaires"},
     *     summary="Créer un secrétaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Créé",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireResponse")
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
    public function store(StoreSecretaireRequest $request)
    {
        Gate::authorize('secretaire.create');

        $secretaire = $this->createSecretaireService->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Secrétaire créé avec succès',
            'data' => new SecretaireResource($secretaire),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/secretaires/{id}",
     *     tags={"Secretaires"},
     *     summary="Afficher un secrétaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireResponse")
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
        $secretaire = $this->secretaireRepository->findById($id);
        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        Gate::authorize('secretaire.view', $secretaire);

        return response()->json([
            'success' => true,
            'message' => 'Détail du secrétaire',
            'data' => new SecretaireResource($secretaire),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/secretaires{id}",
     *     tags={"Secretaires"},
     *     summary="Mettre à jour un secrétaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireResponse")
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
    public function update(UpdateSecretaireRequest $request, string $id)
    {
        $secretaire = $this->secretaireRepository->findById($id);
        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        Gate::authorize('secretaire.update', $secretaire);

        $secretaire = $this->updateSecretaireService->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Secrétaire mis à jour',
            'data' => new SecretaireResource($secretaire),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/secretaires/{id}",
     *     tags={"Secretaires"},
     *     summary="Supprimer un secrétaire",
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
        $secretaire = $this->secretaireRepository->findById($id);
        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        Gate::authorize('secretaire.delete', $secretaire);

        $this->deleteSecretaireService->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Secrétaire supprimé',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/secretaires/{id}/statut",
     *     tags={"Secretaires"},
     *     summary="Changer le statut d'un secrétaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StatutUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SecretaireResponse")
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
        $secretaire = $this->secretaireRepository->findById($id);
        if (!$secretaire) {
            abort(404, 'Secrétaire introuvable');
        }

        Gate::authorize('secretaire.changerStatut', $secretaire);

        $validated = $request->validate([
            'statut' => 'required|in:ACTIF,INACTIF,ENCONGE',
        ]);

        $secretaire = $this->changerStatutSecretaireService->execute($id, $validated['statut']);

        return response()->json([
            'success' => true,
            'message' => 'Statut du secrétaire mis à jour',
            'data' => new SecretaireResource($secretaire),
            'errors' => null,
        ]);
    }
}
