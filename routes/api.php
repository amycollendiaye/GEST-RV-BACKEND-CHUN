<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ActivationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedecinController;
use App\Http\Controllers\Api\SecretaireController;
use App\Http\Controllers\Api\ServiceMedicalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/admin/register', [AdminController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

Route::get('/activation', [ActivationController::class, 'validateToken']);
Route::post('/activation/password', [ActivationController::class, 'updatePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/medecins', [MedecinController::class, 'index']);
    Route::post('/medecins', [MedecinController::class, 'store']);
    Route::get('/medecins/{id}', [MedecinController::class, 'show']);
    Route::put('/medecins/{id}', [MedecinController::class, 'update']);
    Route::delete('/medecins/{id}', [MedecinController::class, 'destroy']);
    Route::patch('/medecins/{id}/statut', [MedecinController::class, 'changerStatut']);
    Route::get('/medecins/{id}/planning', [MedecinController::class, 'planning']);

    Route::get('/secretaires', [SecretaireController::class, 'index']);
    Route::post('/secretaires', [SecretaireController::class, 'store']);
    Route::get('/secretaires/{id}', [SecretaireController::class, 'show']);
    Route::put('/secretaires/{id}', [SecretaireController::class, 'update']);
    Route::delete('/secretaires/{id}', [SecretaireController::class, 'destroy']);
    Route::patch('/secretaires/{id}/statut', [SecretaireController::class, 'changerStatut']);

    Route::get('/services', [ServiceMedicalController::class, 'index']);
    Route::post('/services', [ServiceMedicalController::class, 'store']);
    Route::get('/services/{serviceMedical}', [ServiceMedicalController::class, 'show']);
    Route::put('/services/{serviceMedical}', [ServiceMedicalController::class, 'update']);
    Route::delete('/services/{serviceMedical}', [ServiceMedicalController::class, 'destroy']);
});
