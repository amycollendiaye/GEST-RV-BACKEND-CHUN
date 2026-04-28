<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalAudit\FiltreJournalRequest;
use App\Http\Resources\JournalAuditCollection;
use App\Http\Resources\JournalAuditResource;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use App\Services\JournalAudit\FiltreJournalService;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(name="Journal d'audit")
 */
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
     *     summary="Liste paginée du journal d'audit",
     *     tags={"Journal d'audit"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="type_action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="personel_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_debut", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_fin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Liste des journaux d'audit"),
     *             @OA\Property(property="data", ref="#/components/schemas/JournalAuditCollection"),
     *             @OA\Property(property="errors", type="null")
     *         )
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
        $user = auth()->user();
        if (!$user || strtoupper($user->role) !== 'ADMIN') {
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
     *     summary="Détails d'une entrée du journal",
     *     tags={"Journal d'audit"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du journal"),
     *             @OA\Property(property="data", ref="#/components/schemas/JournalAuditResource"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user || strtoupper($user->role) !== 'ADMIN') {
            abort(403, 'Seul l\'administrateur peut consulter les journaux d\'audit.');
        }

        $journalAudit = $this->journalAuditRepository->findById($id);

        if (!$journalAudit) {
            abort(404, 'Journal d\'audit introuvable.');
        }

        Gate::authorize('journal.view', $journalAudit);

        return response()->json([
            'success' => true,
            'message' => 'Détails du journal',
            'data' => new JournalAuditResource($journalAudit),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/journal/export",
     *     summary="Exporter le journal en CSV",
     *     tags={"Journal d'audit"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fichier CSV",
     *         @OA\MediaType(mediaType="text/csv")
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
        $user = auth()->user();
        if (!$user || strtoupper($user->role) !== 'ADMIN') {
            abort(403, 'Seul l\'administrateur peut consulter les journaux d\'audit.');
        }

        Gate::authorize('journal.export');

        $validated = $request->validated();
        $format = $validated['format'] ?? 'csv';

        return $this->filtreJournalService->exporter($validated, $format);
    }
}
