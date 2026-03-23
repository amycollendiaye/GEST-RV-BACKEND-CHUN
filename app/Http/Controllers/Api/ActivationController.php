<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activation\ActivationPasswordRequest;
use App\Services\Activation\ActivationService;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function __construct(
        private readonly ActivationService $activationService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/activation",
     *     tags={"Activation"},
     *     summary="Valider un token d'activation",
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token valide",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function validateToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $this->activationService->validateToken($request->query('token'));

        return response()->json([
            'success' => true,
            'message' => 'Veuillez changer votre mot de passe',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/activation/password",
     *     tags={"Activation"},
     *     summary="Mettre à jour le mot de passe via token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ActivationPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/ActivationTokenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function updatePassword(ActivationPasswordRequest $request)
    {
        $validated = $request->validated();
        $token = $this->activationService->updatePassword($validated['token'], $validated['password']);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe mis à jour',
            'data' => [
                'token' => $token,
            ],
            'errors' => null,
        ]);
    }
}
