<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceMedicalRequest;
use App\Http\Requests\UpdateServiceMedicalRequest;
use App\Http\Resources\ServiceMedicalCollection;
use App\Http\Resources\ServiceMedicalResource;
use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;
use App\Services\ServiceMedical\CreateServiceMedicalService;
use App\Services\ServiceMedical\DeleteServiceMedicalService;
use App\Services\ServiceMedical\UpdateServiceMedicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceMedicalController extends Controller
{
    public function __construct(
        private readonly ServiceMedicalRepositoryInterface $serviceMedicalRepository,
        private readonly CreateServiceMedicalService $createServiceMedicalService,
        private readonly UpdateServiceMedicalService $updateServiceMedicalService,
        private readonly DeleteServiceMedicalService $deleteServiceMedicalService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     tags={"Services"},
     *     summary="Liste des services",
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
     *         @OA\JsonContent(ref="#/components/schemas/ServiceListResponse")
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
        Gate::authorize('service.viewAny');

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $filters = $request->only([
            'search',
            'statut',
            'service_id',
            'specialite',
            'sort_by',
            'sort_dir',
        ]);

        $services = $this->serviceMedicalRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des services',
            'data' => new ServiceMedicalCollection($services),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services",
     *     tags={"Services"},
     *     summary="Créer un service",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ServiceCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Créé",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResponse")
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
    public function store(StoreServiceMedicalRequest $request)
    {
        Gate::authorize('service.create');

        $service = $this->createServiceMedicalService->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service créé avec succès',
            'data' => new ServiceMedicalResource($service),
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{id}",
     *     tags={"Services"},
     *     summary="Détail du service (avec médecins)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResponse")
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
        $service = $this->serviceMedicalRepository->findById($id);
        if (!$service) {
            abort(404, 'Service introuvable');
        }

        Gate::authorize('service.view', $service);

        return response()->json([
            'success' => true,
            'message' => 'Détail du service',
            'data' => new ServiceMedicalResource($service),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{id}",
     *     tags={"Services"},
     *     summary="Mettre à jour un service",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ServiceUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResponse")
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
    public function update(UpdateServiceMedicalRequest $request, string $id)
    {
        $service = $this->serviceMedicalRepository->findById($id);
        if (!$service) {
            abort(404, 'Service introuvable');
        }

        Gate::authorize('service.update', $service);

        $service = $this->updateServiceMedicalService->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service mis à jour',
            'data' => new ServiceMedicalResource($service),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/services/{id}",
     *     tags={"Services"},
     *     summary="Supprimer un service",
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
        $service = $this->serviceMedicalRepository->findById($id);
        if (!$service) {
            abort(404, 'Service introuvable');
        }

        Gate::authorize('service.delete', $service);

        $this->deleteServiceMedicalService->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Service supprimé',
            'data' => null,
            'errors' => null,
        ]);
    }
}
