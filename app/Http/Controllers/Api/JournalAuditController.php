<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalAudit\FiltreJournalRequest;
use App\Http\Resources\JournalAuditCollection;
use App\Http\Resources\JournalAuditResource;
use App\Models\PersonelHopital;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use App\Services\JournalAudit\FiltreJournalService;
use Illuminate\Support\Facades\Gate;

class JournalAuditController extends Controller
{
    public function __construct(
        private readonly JournalAuditRepositoryInterface $journalAuditRepository,
        private readonly FiltreJournalService $filtreJournalService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/journal",
     *     tags={"Journal Audit"},
     *     summary="Lister les journaux d'audit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="type_action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="personel_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="adresse_ip", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=50, maximum=200)),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", enum={"created_at"})),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des journaux d'audit",
     *         @OA\JsonContent(ref="#/components/schemas/JournalAuditListResponse")
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
    public function index(FiltreJournalRequest $request)
    {
        if (!(auth()->user() instanceof PersonelHopital)) {
            abort(403, 'Seul l\'administrateur peut consulter les journaux d\'audit.');
        }

        Gate::authorize('journal.viewAny');

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 50);

        $journaux = $this->filtreJournalService->paginer($validated, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des journaux d\'audit',
            'data' => new JournalAuditCollection($journaux),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/journal/{id}",
     *     tags={"Journal Audit"},
     *     summary="Afficher le détail d'un journal d'audit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Détail du journal d'audit",
     *         @OA\JsonContent(ref="#/components/schemas/JournalAuditResponse")
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Journal introuvable",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $id)
    {
        if (!(auth()->user() instanceof PersonelHopital)) {
            abort(403, 'Seul l\'administrateur peut consulter les journaux d\'audit.');
        }

        $journalAudit = $this->journalAuditRepository->findById($id);

        if (!$journalAudit) {
            abort(404, 'Journal d\'audit introuvable.');
        }

        Gate::authorize('journal.view', $journalAudit);

        return response()->json([
            'success' => true,
            'message' => 'Détail du journal d\'audit',
            'data' => new JournalAuditResource($journalAudit),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/journal/export",
     *     tags={"Journal Audit"},
     *     summary="Exporter les journaux d'audit en CSV",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="type_action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="personel_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="adresse_ip", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Fichier CSV généré",
     *         @OA\MediaType(
     *             mediaType="text/csv"
     *         )
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
    public function export(FiltreJournalRequest $request)
    {
        if (!(auth()->user() instanceof PersonelHopital)) {
            abort(403, 'Seul l\'administrateur peut consulter les journaux d\'audit.');
        }

        Gate::authorize('journal.export');

        return $this->filtreJournalService->exporterCsv($request->validated());
    }
}
