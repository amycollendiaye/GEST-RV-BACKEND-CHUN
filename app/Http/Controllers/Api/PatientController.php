<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\DossierMedicalResource;
use App\Http\Resources\PatientCollection;
use App\Http\Resources\PatientResource;
use App\Http\Resources\RendezVousCollection;
use App\Models\Patient;
use App\Repositories\Interfaces\PatientRepositoryInterface;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use App\Services\DossierMedical\ConsulterDossierMedicalService;
use App\Services\Patient\CreatePatientService;
use App\Services\Patient\DeletePatientService;
use App\Services\Patient\UpdatePatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientRepositoryInterface $patientRepository,
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly CreatePatientService $createPatientService,
        private readonly UpdatePatientService $updatePatientService,
        private readonly DeletePatientService $deletePatientService,
        private readonly ConsulterDossierMedicalService $consulterDossierMedicalService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/patients",
     *     tags={"Patients"},
     *     summary="Liste des patients",
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
     *         @OA\JsonContent(ref="#/components/schemas/PatientListResponse")
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
        Gate::authorize('patient.viewAny');

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

        $patients = $this->patientRepository->findAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des patients',
            'data' => new PatientCollection($patients),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/patients",
     *     tags={"Patients"},
     *     summary="Créer un patient",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PatientCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Créé",
     *         @OA\JsonContent(ref="#/components/schemas/PatientResponse")
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
    public function store(StorePatientRequest $request)
    {
        Gate::authorize('patient.create');

        $payload = $request->validated();
        $payload['date_naissance'] = $payload['dateNaissance'];
        unset($payload['dateNaissance']);

        $result = $this->createPatientService->execute($payload);

        return response()->json([
            'success' => true,
            'message' => 'Patient créé avec succès',
            'data' => [
                'patient' => new PatientResource($result['patient']),
                'credentials' => $result['credentials'],
            ],
            'errors' => null,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/patients/{id}",
     *     tags={"Patients"},
     *     summary="Détail d'un patient",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PatientResponse")
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
        $patient = $this->patientRepository->findById($id);
        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        Gate::authorize('patient.view', $patient);

        return response()->json([
            'success' => true,
            'message' => 'Détail du patient',
            'data' => new PatientResource($patient),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/patients/{id}",
     *     tags={"Patients"},
     *     summary="Mettre à jour un patient",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PatientUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PatientResponse")
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
    public function update(UpdatePatientRequest $request, string $id)
    {
        $patient = $this->patientRepository->findById($id);
        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        Gate::authorize('patient.update', $patient);

        $payload = $request->validated();
        if (array_key_exists('dateNaissance', $payload)) {
            $payload['date_naissance'] = $payload['dateNaissance'];
            unset($payload['dateNaissance']);
        }

        $patient = $this->updatePatientService->execute($id, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Patient mis à jour avec succès',
            'data' => new PatientResource($patient),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/patients/{id}",
     *     tags={"Patients"},
     *     summary="Supprimer un patient",
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
    public function destroy(string $id)
    {
        $patient = $this->patientRepository->findById($id);
        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        Gate::authorize('patient.delete', $patient);

        $this->deletePatientService->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/patients/mon-profil",
     *     tags={"Patients"},
     *     summary="Profil du patient connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PatientResponse")
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
     *     )
     * )
     */
    public function monProfil()
    {
        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil patient',
            'data' => new PatientResource($user),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/patients/mon-dossier",
     *     tags={"Patients"},
     *     summary="Dossier médical du patient connecté",
     *     security={{"bearerAuth":{}}},
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
    public function monDossier()
    {
        $user = auth()->user();
        if (!$user instanceof Patient) {
            abort(403, 'Non autorisé');
        }

        $dossier = $this->consulterDossierMedicalService->executeByPatient($user->id);
        if (!$dossier) {
            abort(404, 'Dossier médical introuvable');
        }

        return response()->json([
            'success' => true,
            'message' => 'Dossier médical du patient',
            'data' => new DossierMedicalResource($dossier),
            'errors' => null,
        ]);
    }

    public function dossier(string $id)
    {
        $patient = $this->patientRepository->findById($id);
        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        Gate::authorize('patient.view', $patient);

        $dossier = $this->consulterDossierMedicalService->executeByPatient($patient->id);
        if (!$dossier) {
            abort(404, 'Dossier médical introuvable');
        }

        Gate::authorize('dossier.view', $dossier);

        return response()->json([
            'success' => true,
            'message' => 'Dossier médical du patient',
            'data' => new DossierMedicalResource($dossier),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/patients/{id}/rendez-vous",
     *     tags={"Patients"},
     *     summary="Liste des rendez-vous d'un patient",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
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
    public function rendezVous(string $id, Request $request)
    {
        $patient = $this->patientRepository->findById($id);
        if (!$patient) {
            abort(404, 'Patient introuvable');
        }

        Gate::authorize('patient.view', $patient);

        $user = auth()->user();
        if ($user instanceof Patient) {
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

        $rendezVous = $this->rendezVousRepository->findAllByPatient($patient->id, $filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des rendez-vous du patient',
            'data' => new RendezVousCollection($rendezVous),
            'errors' => null,
        ]);
    }
}
