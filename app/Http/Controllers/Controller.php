<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API GEST_RV_CHUN",
 *     description="Documentation des endpoints API"
 * )
 * @OA\Server(
 *     url="/",
 *     description="Serveur local"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="ServiceMini",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="nom", type="string", example="Cardiologie")
 * )
 * @OA\Schema(
 *     schema="Medecin",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="matricule", type="string", example="MED-0001"),
 *     @OA\Property(property="nom", type="string", example="Diop"),
 *     @OA\Property(property="prenom", type="string", example="Awa"),
 *     @OA\Property(property="email", type="string", example="awa.diop@example.com"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567"),
 *     @OA\Property(property="specialite", type="string", example="Cardiologie"),
 *     @OA\Property(property="statut", type="string", example="ACTIF"),
 *     @OA\Property(property="role", type="string", example="MEDECIN"),
 *     @OA\Property(property="first_login", type="boolean", example=true),
 *     @OA\Property(property="service", ref="#/components/schemas/ServiceMini"),
 *     @OA\Property(property="created_at", type="string", example="2024-01-01T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="Secretaire",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="3f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="matricule", type="string", example="SEC-0001"),
 *     @OA\Property(property="nom", type="string", example="Ndiaye"),
 *     @OA\Property(property="prenom", type="string", example="Fatou"),
 *     @OA\Property(property="email", type="string", example="fatou.ndiaye@example.com"),
 *     @OA\Property(property="telephone", type="string", example="+221781234567"),
 *     @OA\Property(property="statut", type="string", example="ACTIF"),
 *     @OA\Property(property="role", type="string", example="SECRETAIRE"),
 *     @OA\Property(property="first_login", type="boolean", example=true),
 *     @OA\Property(property="service", ref="#/components/schemas/ServiceMini"),
 *     @OA\Property(property="created_at", type="string", example="2024-01-01T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="ServiceMedical",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="nom", type="string", example="Cardiologie"),
 *     @OA\Property(property="description", type="string", example="Service de cardiologie"),
 *     @OA\Property(property="heure_ouverture", type="string", example="08:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="18:00"),
 *     @OA\Property(property="etat", type="string", example="DISPONIBLE"),
 *     @OA\Property(
 *         property="medecins",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Medecin")
 *     ),
 *     @OA\Property(property="created_at", type="string", example="2024-01-01T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-01-01T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=120),
 *     @OA\Property(property="last_page", type="integer", example=8)
 * )
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string", nullable=true, example="http://localhost/api/v1/medecins?page=1"),
 *     @OA\Property(property="last", type="string", nullable=true, example="http://localhost/api/v1/medecins?page=8"),
 *     @OA\Property(property="prev", type="string", nullable=true, example=null),
 *     @OA\Property(property="next", type="string", nullable=true, example="http://localhost/api/v1/medecins?page=2")
 * )
 * @OA\Schema(
 *     schema="PaginatedMedecins",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Medecin")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="PaginatedSecretaires",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Secretaire")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="PaginatedServices",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ServiceMedical")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Erreur"),
 *     @OA\Property(property="data", nullable=true),
 *     @OA\Property(property="errors", type="object", nullable=true)
 * )
 * @OA\Schema(
 *     schema="MessageResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Opération réussie"),
 *     @OA\Property(property="data", nullable=true),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ActivationTokenResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Mot de passe mis à jour"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="token", type="string", example="1|abc123...")
 *     ),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="MedecinResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du médecin"),
 *     @OA\Property(property="data", ref="#/components/schemas/Medecin"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="MedecinListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des médecins"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedMedecins"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="SecretaireResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du secrétaire"),
 *     @OA\Property(property="data", ref="#/components/schemas/Secretaire"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="SecretaireListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des secrétaires"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedSecretaires"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ServiceResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du service"),
 *     @OA\Property(property="data", ref="#/components/schemas/ServiceMedical"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ServiceListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des services"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedServices"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PlanningResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Planning du médecin"),
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ActivationPasswordRequest",
 *     type="object",
 *     required={"token","password","password_confirmation"},
 *     @OA\Property(property="token", type="string", example="abc123"),
 *     @OA\Property(property="password", type="string", format="password", example="P@ssw0rd!"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="P@ssw0rd!")
 * )
 * @OA\Schema(
 *     schema="MedecinCreateRequest",
 *     type="object",
 *     required={"nom","prenom","email","telephone","specialite","service_medical_id"},
 *     @OA\Property(property="nom", type="string", example="Diop"),
 *     @OA\Property(property="prenom", type="string", example="Awa"),
 *     @OA\Property(property="email", type="string", example="awa.diop@example.com"),
 *     @OA\Property(property="telephone", type="string", example="771234567"),
 *     @OA\Property(property="specialite", type="string", example="Cardiologie"),
 *     @OA\Property(property="service_medical_id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c")
 * )
 * @OA\Schema(
 *     schema="MedecinUpdateRequest",
 *     type="object",
 *     @OA\Property(property="nom", type="string", example="Diop"),
 *     @OA\Property(property="prenom", type="string", example="Awa"),
 *     @OA\Property(property="email", type="string", example="awa.diop@example.com"),
 *     @OA\Property(property="telephone", type="string", example="771234567"),
 *     @OA\Property(property="specialite", type="string", example="Cardiologie"),
 *     @OA\Property(property="service_medical_id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c")
 * )
 * @OA\Schema(
 *     schema="SecretaireCreateRequest",
 *     type="object",
 *     required={"nom","prenom","email","telephone","service_medical_id"},
 *     @OA\Property(property="nom", type="string", example="Ndiaye"),
 *     @OA\Property(property="prenom", type="string", example="Fatou"),
 *     @OA\Property(property="email", type="string", example="fatou.ndiaye@example.com"),
 *     @OA\Property(property="telephone", type="string", example="781234567"),
 *     @OA\Property(property="service_medical_id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c")
 * )
 * @OA\Schema(
 *     schema="SecretaireUpdateRequest",
 *     type="object",
 *     @OA\Property(property="nom", type="string", example="Ndiaye"),
 *     @OA\Property(property="prenom", type="string", example="Fatou"),
 *     @OA\Property(property="email", type="string", example="fatou.ndiaye@example.com"),
 *     @OA\Property(property="telephone", type="string", example="781234567"),
 *     @OA\Property(property="service_medical_id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c")
 * )
 * @OA\Schema(
 *     schema="ServiceCreateRequest",
 *     type="object",
 *     required={"nom","heure_ouverture","heure_fermeture"},
 *     @OA\Property(property="nom", type="string", example="Cardiologie"),
 *     @OA\Property(property="description", type="string", example="Service de cardiologie"),
 *     @OA\Property(property="heure_ouverture", type="string", example="08:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="18:00"),
 *     @OA\Property(property="etat", type="string", example="DISPONIBLE")
 * )
 * @OA\Schema(
 *     schema="ServiceUpdateRequest",
 *     type="object",
 *     @OA\Property(property="nom", type="string", example="Cardiologie"),
 *     @OA\Property(property="description", type="string", example="Service de cardiologie"),
 *     @OA\Property(property="heure_ouverture", type="string", example="08:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="18:00"),
 *     @OA\Property(property="etat", type="string", example="DISPONIBLE")
 * )
 * @OA\Schema(
 *     schema="StatutUpdateRequest",
 *     type="object",
 *     required={"statut"},
 *     @OA\Property(property="statut", type="string", enum={"ACTIF","INACTIF","ENCONGE"})
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
