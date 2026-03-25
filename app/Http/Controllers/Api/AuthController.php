<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Patient;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\LoginService;
use App\Services\Auth\LogoutService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService,
        private readonly ChangePasswordService $changePasswordService,
        private readonly LogoutService $logoutService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Connexion",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","password"},
     *             @OA\Property(property="login", type="string", example="admfann2025-0001"),
     *             @OA\Property(property="password", type="string", format="password", example="P@ssw0rd!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Changement de mot de passe requis",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $result = $this->loginService->execute(
            $request->validated()['login'],
            $request->validated()['password']
        );

        if (!empty($result['force_password_change'])) {
            return response()->json([
                'success' => false,
                'message' => 'Changement de mot de passe requis',
                'data' => [
                    'force_password_change' => true,
                ],
                'errors' => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token' => $result['token'],
                'user' => [
                    'id' => $result['user']->id,
                    'login' => $result['user'] instanceof Patient
                        ? $result['user']->login
                        : $result['user']->infosConnexion?->login,
                    'role' => $result['user'] instanceof Patient
                        ? 'PATIENT'
                        : $result['user']->role,
                ],
            ],
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/change-password",
     *     tags={"Auth"},
     *     summary="Changer le mot de passe (première connexion)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","current_password","password","password_confirmation"},
     *             @OA\Property(property="login", type="string", example="admfann2025-0001"),
     *             @OA\Property(property="current_password", type="string", format="password", example="P@ssw0rd!"),
     *             @OA\Property(property="password", type="string", format="password", example="N3wP@ssw0rd!"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="N3wP@ssw0rd!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe changé",
     *         @OA\JsonContent(ref="#/components/schemas/ActivationTokenResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Mot de passe déjà changé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $validated = $request->validated();
        $user = $this->changePasswordService->execute(
            $validated['login'],
            $validated['current_password'],
            $validated['password']
        );

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe changé avec succès',
            'data' => [
                'token' => $token,
            ],
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Déconnexion",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $this->logoutService->execute($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
            'data' => null,
            'errors' => null,
        ]);
    }
}
