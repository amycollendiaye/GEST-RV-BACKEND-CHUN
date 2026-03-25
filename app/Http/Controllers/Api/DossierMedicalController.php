<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DossierMedicalModificationInterditeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DossierMedical\UpdateDossierMedicalRequest;
use App\Http\Resources\DossierMedicalResource;
use App\Models\PersonelHopital;
use App\Services\DossierMedical\ConsulterDossierMedicalService;
use App\Services\DossierMedical\UpdateDossierMedicalService;
use Illuminate\Support\Facades\Gate;

class DossierMedicalController extends Controller
{
    public function __construct(
        private readonly ConsulterDossierMedicalService $consulterDossierMedicalService,
        private readonly UpdateDossierMedicalService $updateDossierMedicalService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/dossiers/{id}",
     *     tags={"Dossiers"},
     *     summary="Consulter un dossier médical",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/DossierMedicalResponse")
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
        $dossier = $this->consulterDossierMedicalService->executeById($id);
        if (!$dossier) {
            abort(404, 'Dossier médical introuvable');
        }

        Gate::authorize('dossier.view', $dossier);

        return response()->json([
            'success' => true,
            'message' => 'Dossier médical',
            'data' => new DossierMedicalResource($dossier),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/dossiers/{id}",
     *     tags={"Dossiers"},
     *     summary="Mettre à jour un dossier médical",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DossierMedicalUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/DossierMedicalResponse")
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
    public function update(UpdateDossierMedicalRequest $request, string $id)
    {
        $dossier = $this->consulterDossierMedicalService->executeById($id);
        if (!$dossier) {
            abort(404, 'Dossier médical introuvable');
        }

        $user = auth()->user();
        if (!$user instanceof PersonelHopital || $user->role !== 'MEDECIN') {
            throw new DossierMedicalModificationInterditeException();
        }

        Gate::authorize('dossier.update', $dossier);

        $dossier = $this->updateDossierMedicalService->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Dossier médical mis à jour',
            'data' => new DossierMedicalResource($dossier),
            'errors' => null,
        ]);
    }
}
