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
 *     schema="Patient",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="7f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="matricule", type="string", example="PAT-FANN-2026-0001"),
 *     @OA\Property(property="nom", type="string", example="Ba"),
 *     @OA\Property(property="prenom", type="string", example="Moussa"),
 *     @OA\Property(property="email", type="string", example="moussa.ba@example.com"),
 *     @OA\Property(property="telephone", type="string", example="771234567"),
 *     @OA\Property(property="date_naissance", type="string", example="1990-05-12"),
 *     @OA\Property(property="statut", type="string", example="ACTIF"),
 *     @OA\Property(property="first_login", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", example="2024-01-01T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="PaginatedPatients",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Patient")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="RendezVous",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="5f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="motif", type="string", example="Douleurs thoraciques"),
 *     @OA\Property(property="statut", type="string", example="PLANIFIER"),
 *     @OA\Property(property="planning_medecin_id", type="string", nullable=true, example="1f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4d"),
 *     @OA\Property(property="date_rendez_vous", type="string", example="2026-04-10T09:00:00Z"),
 *     @OA\Property(property="service", ref="#/components/schemas/ServiceMini"),
 *     @OA\Property(
 *         property="patient",
 *         type="object",
 *         @OA\Property(property="id", type="string", example="7f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *         @OA\Property(property="nom", type="string", example="Ba"),
 *         @OA\Property(property="prenom", type="string", example="Moussa"),
 *         @OA\Property(property="matricule", type="string", example="PAT-FANN-2026-0001")
 *     ),
 *     @OA\Property(
 *         property="medecin",
 *         nullable=true,
 *         type="object",
 *         @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *         @OA\Property(property="nom", type="string", example="Diop"),
 *         @OA\Property(property="prenom", type="string", example="Awa"),
 *         @OA\Property(property="specialite", type="string", example="Cardiologie")
 *     ),
 *     @OA\Property(property="created_at", type="string", example="2026-03-23T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="PaginatedRendezVous",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RendezVous")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="Consultation",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="1f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="tension_artielle", type="string", example="12/8"),
 *     @OA\Property(property="poids", type="number", example=70.5),
 *     @OA\Property(property="temperature", type="number", example=36.8),
 *     @OA\Property(property="sumptomes", type="string", example="Douleur thoracique"),
 *     @OA\Property(property="diagnostic", type="string", example="Hypertension"),
 *     @OA\Property(property="traitement", type="string", example="Traitement A"),
 *     @OA\Property(property="observations", type="string", nullable=true, example="Observation"),
 *     @OA\Property(property="date_heure", type="string", example="2026-03-23T10:00:00Z"),
 *     @OA\Property(property="statut", type="string", example="EN_COURS"),
 *     @OA\Property(property="date_rendez_vous", type="string", example="2026-03-23T09:00:00Z"),
 *     @OA\Property(property="statut_rendez", type="string", example="FAIT"),
 *     @OA\Property(
 *         property="patient",
 *         type="object",
 *         @OA\Property(property="id", type="string", example="7f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *         @OA\Property(property="nom", type="string", example="Ba"),
 *         @OA\Property(property="prenom", type="string", example="Moussa")
 *     ),
 *     @OA\Property(
 *         property="medecin",
 *         type="object",
 *         @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *         @OA\Property(property="nom", type="string", example="Diop"),
 *         @OA\Property(property="prenom", type="string", example="Awa"),
 *         @OA\Property(property="specialite", type="string", example="Cardiologie")
 *     ),
 *     @OA\Property(property="created_at", type="string", example="2026-03-23T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="PaginatedConsultations",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Consultation")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="PlanningMedecin",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="1f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4d"),
 *     @OA\Property(property="date", type="string", example="25/03/2026"),
 *     @OA\Property(property="heure_ouverture", type="string", example="08:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="12:00"),
 *     @OA\Property(property="capacite", type="integer", example=8),
 *     @OA\Property(property="nombre_rendez_vous_attribues", type="integer", example=3),
 *     @OA\Property(property="places_restantes", type="integer", example=5),
 *     @OA\Property(property="est_complet", type="boolean", example=false),
 *     @OA\Property(
 *         property="medecin",
 *         type="object",
 *         @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *         @OA\Property(property="nom", type="string", example="Diop"),
 *         @OA\Property(property="prenom", type="string", example="Awa"),
 *         @OA\Property(property="specialite", type="string", example="Cardiologie")
 *     ),
 *     @OA\Property(property="service", ref="#/components/schemas/ServiceMini"),
 *     @OA\Property(property="created_at", type="string", example="2026-03-23T10:00:00Z")
 * )
 * @OA\Schema(
 *     schema="PaginatedPlannings",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PlanningMedecin")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="DossierMedical",
 *     type="object",
 *     @OA\Property(
 *         property="identification",
 *         type="object",
 *         @OA\Property(property="id", type="string"),
 *         @OA\Property(property="numero_dossier", type="string"),
 *         @OA\Property(property="created_at", type="string"),
 *         @OA\Property(property="updated_at", type="string")
 *     ),
 *     @OA\Property(
 *         property="patient",
 *         type="object",
 *         @OA\Property(property="id", type="string"),
 *         @OA\Property(property="matricule", type="string"),
 *         @OA\Property(property="nom", type="string"),
 *         @OA\Property(property="prenom", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="telephone", type="string"),
 *         @OA\Property(property="date_naissance", type="string"),
 *         @OA\Property(property="age", type="integer"),
 *         @OA\Property(property="adresse", type="string")
 *     ),
 *     @OA\Property(
 *         property="informations_medicales",
 *         type="object",
 *         @OA\Property(property="groupe_sanguin", type="string", nullable=true),
 *         @OA\Property(property="antecedents_medicaux", type="string", nullable=true),
 *         @OA\Property(property="antecedents_chirurgicaux", type="string", nullable=true),
 *         @OA\Property(property="antecedents_familiaux", type="string", nullable=true),
 *         @OA\Property(property="allergies", type="string", nullable=true),
 *         @OA\Property(property="maladies_chroniques", type="string", nullable=true),
 *         @OA\Property(property="traitements_en_cours", type="string", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="statistiques",
 *         type="object",
 *         @OA\Property(property="nombre_consultations", type="integer"),
 *         @OA\Property(property="nombre_rendez_vous_total", type="integer"),
 *         @OA\Property(property="nombre_rendez_vous_annules", type="integer"),
 *         @OA\Property(property="date_derniere_consultation", type="string", nullable=true),
 *         @OA\Property(property="date_premier_rendez_vous", type="string", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="derniere_consultation",
 *         nullable=true,
 *         type="object",
 *         @OA\Property(property="date_heure", type="string"),
 *         @OA\Property(property="statut", type="string"),
 *         @OA\Property(
 *             property="medecin",
 *             type="object",
 *             @OA\Property(property="nom", type="string"),
 *             @OA\Property(property="prenom", type="string")
 *         ),
 *         @OA\Property(
 *             property="service",
 *             type="object",
 *             @OA\Property(property="nom", type="string")
 *         ),
 *         @OA\Property(property="sumptomes", type="string"),
 *         @OA\Property(property="tension_artielle", type="string", nullable=true),
 *         @OA\Property(property="poids", type="number", nullable=true),
 *         @OA\Property(property="temperature", type="number", nullable=true),
 *         @OA\Property(property="diagnostic", type="string", nullable=true),
 *         @OA\Property(property="traitement", type="string", nullable=true),
 *         @OA\Property(property="observations", type="string", nullable=true)
 *     )
 * )
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Non authentifie"),
 *     @OA\Property(property="data", nullable=true),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="type", type="string", example="authentication"),
 *         @OA\Property(
 *             property="detail",
 *             type="string",
 *             example="Authentification requise pour acceder a cette ressource."
 *         )
 *     )
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
 *     schema="PatientResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du patient"),
 *     @OA\Property(property="data", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PatientListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des patients"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedPatients"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="RendezVousResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du rendez-vous"),
 *     @OA\Property(property="data", ref="#/components/schemas/RendezVous"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="RendezVousListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des rendez-vous"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedRendezVous"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ConsultationResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail de la consultation"),
 *     @OA\Property(property="data", ref="#/components/schemas/Consultation"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ConsultationListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des consultations"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedConsultations"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="DossierMedicalResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Dossier médical"),
 *     @OA\Property(property="data", ref="#/components/schemas/DossierMedical"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PlanningMedecinResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Detail du planning"),
 *     @OA\Property(property="data", ref="#/components/schemas/PlanningMedecin"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PlanningMedecinListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des plannings"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedPlannings"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PlanningResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Planning du medecin"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PlanningMedecin")),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ConsultationReprogrammationResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Rendez-vous de suivi reprogramme avec succes"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="consultation", ref="#/components/schemas/Consultation"),
 *         @OA\Property(property="nouveau_rendez_vous", ref="#/components/schemas/RendezVous")
 *     ),
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
 *     schema="PatientCreateRequest",
 *     type="object",
 *     required={"nom","prenom","email","telephone","dateNaissance","adresse"},
 *     @OA\Property(property="nom", type="string", example="Ba"),
 *     @OA\Property(property="prenom", type="string", example="Moussa"),
 *     @OA\Property(property="email", type="string", example="moussa.ba@example.com"),
 *     @OA\Property(property="telephone", type="string", example="771234567"),
 *     @OA\Property(property="dateNaissance", type="string", example="1990-05-12"),
 *     @OA\Property(property="adresse", type="string", example="Dakar")
 * )
 * @OA\Schema(
 *     schema="PatientUpdateRequest",
 *     type="object",
 *     @OA\Property(property="nom", type="string", example="Ba"),
 *     @OA\Property(property="prenom", type="string", example="Moussa"),
 *     @OA\Property(property="email", type="string", example="moussa.ba@example.com"),
 *     @OA\Property(property="telephone", type="string", example="771234567"),
 *     @OA\Property(property="dateNaissance", type="string", example="1990-05-12"),
 *     @OA\Property(property="adresse", type="string", example="Dakar"),
 *     @OA\Property(property="statut", type="string", example="ACTIF")
 * )
 * @OA\Schema(
 *     schema="PlanningMedecinCreateRequest",
 *     type="object",
 *     required={"date","heure_ouverture","heure_fermeture","capacite"},
 *     @OA\Property(property="date", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="heure_ouverture", type="string", example="08:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="12:00"),
 *     @OA\Property(property="capacite", type="integer", example=8)
 * )
 * @OA\Schema(
 *     schema="PlanningMedecinUpdateRequest",
 *     type="object",
 *     @OA\Property(property="date", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="heure_ouverture", type="string", example="09:00"),
 *     @OA\Property(property="heure_fermeture", type="string", example="13:00"),
 *     @OA\Property(property="capacite", type="integer", example=10)
 * )
 * @OA\Schema(
 *     schema="RendezVousCreateRequest",
 *     type="object",
 *     required={"service_medical_id","motif"},
 *     @OA\Property(property="service_medical_id", type="string", example="2f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="motif", type="string", example="Douleurs thoraciques")
 * )
 * @OA\Schema(
 *     schema="RendezVousStatutUpdateRequest",
 *     type="object",
 *     required={"statut"},
 *     @OA\Property(property="statut", type="string", enum={"PLANIFIER","FAIT","ANNULER"})
 * )
 * @OA\Schema(
 *     schema="RendezVousReprogrammationRequest",
 *     type="object",
 *     required={"motif_suivi"},
 *     @OA\Property(property="motif_suivi", type="string", example="Controle post traitement")
 * )
 * @OA\Schema(
 *     schema="DossierMedicalUpdateRequest",
 *     type="object",
 *     @OA\Property(property="groupe_sanguin", type="string", example="O+"),
 *     @OA\Property(property="antecedents_medicaux", type="string", example="Hypertension"),
 *     @OA\Property(property="antecedents_chirurgicaux", type="string", example="Aucun"),
 *     @OA\Property(property="antecedents_familiaux", type="string", example="Diabète"),
 *     @OA\Property(property="allergies", type="string", example="Aucune"),
 *     @OA\Property(property="maladies_chroniques", type="string", example="Asthme"),
 *     @OA\Property(property="traitements_en_cours", type="string", example="Traitement A")
 * )
 * @OA\Schema(
 *     schema="ConsultationCreateRequest",
 *     type="object",
 *     required={"rendez_vous_id","tension_artielle","poids","temperature","sumptomes","diagnostic","traitement"},
 *     @OA\Property(property="rendez_vous_id", type="string", example="5f2c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="tension_artielle", type="string", example="12/8"),
 *     @OA\Property(property="poids", type="number", example=70.5),
 *     @OA\Property(property="temperature", type="number", example=36.8),
 *     @OA\Property(property="sumptomes", type="string", example="Douleur thoracique"),
 *     @OA\Property(property="diagnostic", type="string", example="Hypertension"),
 *     @OA\Property(property="traitement", type="string", example="Traitement A"),
 *     @OA\Property(property="observations", type="string", example="Observation"),
 *     @OA\Property(property="mise_a_jour_dossier", ref="#/components/schemas/DossierMedicalUpdateRequest")
 * )
 * @OA\Schema(
 *     schema="ConsultationUpdateRequest",
 *     type="object",
 *     @OA\Property(property="tensionArtielle", type="string", example="12/8"),
 *     @OA\Property(property="poids", type="number", example=70.5),
 *     @OA\Property(property="temperature", type="number", example=36.8),
 *     @OA\Property(property="sumptomes", type="string", example="Douleur thoracique"),
 *     @OA\Property(property="diagnostic", type="string", example="Hypertension"),
 *     @OA\Property(property="traitement", type="string", example="Traitement A"),
 *     @OA\Property(property="observations", type="string", example="Observation")
 * )
 * @OA\Schema(
 *     schema="ConsultationClotureRequest",
 *     type="object",
 *     @OA\Property(property="mise_a_jour_dossier", ref="#/components/schemas/DossierMedicalUpdateRequest")
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
 * @OA\Schema(
 *     schema="JournalAuditAuteur",
 *     type="object",
 *     nullable=true,
 *     @OA\Property(property="id", type="string", example="9f8c9c2b-2d2a-4e75-b1a6-1a2f1e2a3b4c"),
 *     @OA\Property(property="nom", type="string", example="Diop"),
 *     @OA\Property(property="prenom", type="string", example="Awa"),
 *     @OA\Property(property="matricule", type="string", example="MED-0001"),
 *     @OA\Property(property="role", type="string", example="ADMIN")
 * )
 * @OA\Schema(
 *     schema="JournalAudit",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=125),
 *     @OA\Property(property="type_action", type="string", example="CREATIONRV"),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         additionalProperties=true,
 *         example={
 *             "matricule_patient":"PAT-FANN-2026-0001",
 *             "nom_patient":"Moussa Ba",
 *             "service_nom":"Cardiologie",
 *             "date_rendez_vous":"2026-04-10",
 *             "heure_approximative":"09:00:00",
 *             "medecin_nom":"Awa Diop"
 *         }
 *     ),
 *     @OA\Property(property="adresse_ip", type="string", nullable=true, example="127.0.0.1"),
 *     @OA\Property(property="user_agent", type="string", nullable=true, example="Mozilla/5.0"),
 *     @OA\Property(property="auteur", ref="#/components/schemas/JournalAuditAuteur"),
 *     @OA\Property(property="created_at", type="string", example="25/03/2026 14:30:00")
 * )
 * @OA\Schema(
 *     schema="PaginatedJournalAudits",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/JournalAudit")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 * @OA\Schema(
 *     schema="JournalAuditResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Détail du journal d'audit"),
 *     @OA\Property(property="data", ref="#/components/schemas/JournalAudit"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="JournalAuditListResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Liste des journaux d'audit"),
 *     @OA\Property(property="data", ref="#/components/schemas/PaginatedJournalAudits"),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="StatistiquesAdminResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Statistiques administrateur"),
 *     @OA\Property(property="data", type="object", additionalProperties=true),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="StatistiquesMedecinResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Statistiques médecin"),
 *     @OA\Property(property="data", type="object", additionalProperties=true),
 *     @OA\Property(property="errors", nullable=true)
 * )
 * @OA\Schema(
 *     schema="StatistiquesSecretaireResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Statistiques secrétaire"),
 *     @OA\Property(property="data", type="object", additionalProperties=true),
 *     @OA\Property(property="errors", nullable=true)
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
