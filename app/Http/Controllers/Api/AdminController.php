<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Services\Admin\CreateAdminService;

class AdminController extends Controller
{
    public function __construct(
        private readonly CreateAdminService $createAdminService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/admin/register",
     *     tags={"Admin"},
     *     summary="Créer le premier administrateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","email","telephone"},
     *             @OA\Property(property="nom", type="string", example="Diop"),
     *             @OA\Property(property="prenom", type="string", example="Awa"),
     *             @OA\Property(property="email", type="string", example="admin@example.com"),
     *             @OA\Property(property="telephone", type="string", example="771234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Administrateur créé",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Administrateur déjà existant",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function register(RegisterAdminRequest $request)
    {
        $admin = $this->createAdminService->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Administrateur créé avec succès',
            'data' => [
                'id' => $admin->id,
                'matricule' => $admin->matricule,
                'login' => $admin->infosConnexion?->login,
                'email' => $admin->email,
            ],
            'errors' => null,
        ], 201);
    }
}
