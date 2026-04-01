<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ActivationController;
use App\Http\Controllers\Api\AttributionRendezVousController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthMeController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DossierMedicalController;
use App\Http\Controllers\Api\JournalAuditController;
use App\Http\Controllers\Api\MedecinController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PlanningMedecinController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\SecretaireController;
use App\Http\Controllers\Api\ServiceMedicalController;
use App\Http\Controllers\Api\StatistiquesAdminController;
use App\Http\Controllers\Api\StatistiquesMedecinController;
use App\Http\Controllers\Api\StatistiquesSecretaireController;

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
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('journal.auth');
Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

Route::get('/activation', [ActivationController::class, 'validateToken']);
Route::post('/activation/password', [ActivationController::class, 'updatePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('journal.auth');
    Route::get("/auth/me", [AuthMeController::class, 'me']);
    Route::get('/auth/me/admin', [AuthMeController::class, 'meAdmin']);
    Route::get('/auth/me/medecin', [AuthMeController::class, 'meMedecin']);
    Route::get('/auth/me/secretaire', [AuthMeController::class, 'meSecretaire']);
    Route::get('/auth/me/patient', [AuthMeController::class, 'mePatient']);

    Route::get('/journal/export', [JournalAuditController::class, 'export']);
    Route::get('/journal', [JournalAuditController::class, 'index']);
    Route::get('/journal/{id}', [JournalAuditController::class, 'show']);

    Route::get('/statistiques/admin', StatistiquesAdminController::class);
    Route::get('/statistiques/medecin', StatistiquesMedecinController::class);
    Route::get('/statistiques/secretaire', StatistiquesSecretaireController::class);

    Route::post('/plannings', [PlanningMedecinController::class, 'store']);
    Route::get('/plannings/mes-plannings', [PlanningMedecinController::class, 'mesPlannings']);
    Route::get('/plannings', [PlanningMedecinController::class, 'index']);
    Route::get('/plannings/{id}', [PlanningMedecinController::class, 'show']);
    Route::put('/plannings/{id}', [PlanningMedecinController::class, 'update']);
    Route::delete('/plannings/{id}', [PlanningMedecinController::class, 'destroy']);
    Route::get('/plannings/{id}/rendez-vous', [PlanningMedecinController::class, 'rendezVous']);

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

    Route::get('/services/disponibles', [RendezVousController::class, 'servicesDisponibles']);
    Route::get('/services', [ServiceMedicalController::class, 'index']);
    Route::post('/services', [ServiceMedicalController::class, 'store']);
    Route::get('/services/{serviceMedical}', [ServiceMedicalController::class, 'show'])->whereUuid('serviceMedical');
    Route::put('/services/{serviceMedical}', [ServiceMedicalController::class, 'update'])->whereUuid('serviceMedical');
    Route::delete('/services/{serviceMedical}', [ServiceMedicalController::class, 'destroy'])->whereUuid('serviceMedical');

    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/mon-profil', [PatientController::class, 'monProfil']);
    Route::get('/patients/mon-dossier', [PatientController::class, 'monDossier']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::put('/patients/{id}', [PatientController::class, 'update']);
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
    Route::get('/patients/{id}/rendez-vous', [PatientController::class, 'rendezVous']);

    Route::post('/rendez-vous', [AttributionRendezVousController::class, 'store']);
    Route::get('/rendez-vous', [RendezVousController::class, 'index']);
    Route::get('/rendez-vous/mes-rendez-vous', [RendezVousController::class, 'mesRendezVous']);
    Route::get('/rendez-vous/{id}', [RendezVousController::class, 'show']);
    Route::patch('/rendez-vous/{id}/annuler', [RendezVousController::class, 'annuler']);
    Route::patch('/rendez-vous/{id}/statut', [RendezVousController::class, 'changerStatut']);

    Route::get('/dossiers/{id}', [DossierMedicalController::class, 'show']);
    Route::put('/dossiers/{id}', [DossierMedicalController::class, 'update']);

    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::get('/consultations/{id}', [ConsultationController::class, 'show']);
    Route::put('/consultations/{id}', [ConsultationController::class, 'update']);
    Route::patch('/consultations/{id}/cloturer', [ConsultationController::class, 'cloturer']);
    Route::patch('/consultations/{id}/reprogrammer', [ConsultationController::class, 'reprogrammer']);
});
