# Architecture UML du projet GEST_RV_CHUN

Ce document présente l'architecture du projet `GEST_RV_CHUN` sur un plan UML, en s'appuyant sur la structure actuelle du code Laravel.

## 1. Diagramme de composants UML


```plantuml
@startuml
skinparam componentStyle rectangle

actor "Utilisateur / Client API" as Client

component "Routes API\nroutes/api.php" as Routes
component "Middlewares\nAuth, Journal, Encryption" as Middleware
component "Controllers API\napp/Http/Controllers/Api" as Controllers
component "Form Requests\napp/Http/Requests" as Requests
component "Policies / Gates\napp/Policies\nAuthServiceProvider" as Policies
component "Services metier\napp/Services" as Services
component "Repositories\napp/Repositories" as Repositories
component "Modeles Eloquent\napp/Models" as Models
database "Base de donnees\nSQLite / MySQL" as DB
component "Events / Listeners\napp/Events + app/Listeners" as Events
component "Notifications\nMail / SMS" as Notifications
component "Resources API\napp/Http/Resources" as Resources
component "Observers\napp/Observers" as Observers

Client --> Routes
Routes --> Middleware
Middleware --> Controllers
Controllers --> Requests
Controllers --> Policies
Controllers --> Services
Controllers --> Resources
Services --> Repositories
Repositories --> Models
Models --> DB
Services --> Events
Events --> Notifications
Models --> Observers
Observers --> DB
@enduml
```

## 2. Diagramme de packages UML

```plantuml
@startuml
package "GEST_RV_CHUN" {
  package "app" {
    package "Http" {
      package "Controllers/Api" as Controllers
      package "Requests" as Requests
      package "Middleware" as Middleware
      package "Resources" as Resources
    }

    package "Models" as Models
    package "Services" as Services
    package "Repositories" as Repositories
    package "Policies" as Policies
    package "Events" as Events
    package "Listeners" as Listeners
    package "Observers" as Observers
    package "Providers" as Providers
    package "Exceptions" as Exceptions
  }

  package "routes" {
    [api.php]
    [web.php]
  }

  package "database" {
    package "migrations" as Migrations
    package "seeders" as Seeders
    package "factories" as Factories
  }

  package "resources" {
    package "views/emails" as EmailViews
    package "js" as Js
    package "css" as Css
  }

  package "config" as Config
  package "public" as Public
  package "storage" as Storage
}

[api.php] --> Controllers
Controllers --> Requests
Controllers --> Services
Controllers --> Policies
Controllers --> Resources
Services --> Repositories
Repositories --> Models
Models --> Migrations
Events --> Listeners
Listeners --> EmailViews
Providers --> Policies
Providers --> Events
@enduml
```

## 3. Diagramme de classes métier simplifié

```plantuml
@startuml

class PersonnelHopital {
  +id: uuid
  +nom: string
  +prenom: string
  +role: string
  +telephone: string
  +email: string
}

class Patient {
  +id: uuid
  +nom: string
  +prenom: string
  +telephone: string
}

class ServiceMedical {
  +id: uuid
  +nom: string
  +description: string
}

class PlanningMedecin {
  +id: uuid
  +date: date
  +heure_debut: time
  +heure_fin: time
  +statut: string
}

class RendezVous {
  +id: uuid
  +date_heure: datetime
  +statut: string
  +motif: string
}

class Consultation {
  +id: uuid
  +diagnostic: string
  +traitement: string
  +statut: string
}

class DossierMedical {
  +id: uuid
  +numero_dossier: string
  +antecedents: text
}

class JournalAudit {
  +id: uuid
  +action: string
  +description: string
  +date_action: datetime
}

PersonnelHopital "1" --> "0..*" PlanningMedecin : gere
PersonnelHopital "1" --> "0..*" RendezVous : prend_en_charge
ServiceMedical "1" --> "0..*" PersonnelHopital : regroupe
Patient "1" --> "1" DossierMedical : possede
Patient "1" --> "0..*" RendezVous : demande
RendezVous "1" --> "0..1" Consultation : donne_lieu_a
PlanningMedecin "1" --> "0..*" RendezVous : contient
JournalAudit --> PersonnelHopital : trace_action

@enduml
```

## 4. Lecture architecturale

Le projet suit principalement le chemin suivant :

1. Le client appelle une route dans `routes/api.php`.
2. La route traverse les middlewares de securite et de journalisation.
3. Le controller API recoit la requete et s'appuie sur une `FormRequest` pour la validation.
4. Les autorisations sont controlees via `Policies` et `Gates`.
5. La logique metier est executee dans `app/Services`.
6. Les acces aux donnees sont centralises dans `app/Repositories`.
7. Les entites et relations sont representees par les `Models`.
8. La reponse sortante est formatee par les `Resources`.
9. Certains traitements declenchent des `Events` puis des `Listeners` pour les notifications email/SMS.

## 5. Fichiers pivots a citer dans un memoire

- `routes/api.php` : point d'entree des fonctionnalites metier.
- `app/Http/Controllers/Api/` : orchestration des cas d'usage exposes par l'API.
- `app/Http/Requests/` : validation des donnees entrantes.
- `app/Services/` : coeur de la logique metier.
- `app/Repositories/` : abstraction des acces aux donnees.
- `app/Models/` : modelisation des entites du domaine medical.
- `app/Policies/` et `app/Providers/AuthServiceProvider.php` : gestion des droits d'acces.
- `app/Events/` et `app/Listeners/` : traitements asynchrones ou decouples.
- `app/Observers/` : reactions automatiques sur les modeles.
- `database/migrations/` : structure physique de la base de donnees.
- `resources/views/emails/admin-credentials.blade.php` : gabarit d'email d'activation/identifiants.

## 6. Modules fonctionnels visibles dans le code

- Gestion des utilisateurs : administrateurs, medecins, secretaires, patients.
- Authentification et activation : login, changement de mot de passe, activation par token.
- Gestion des services medicaux.
- Gestion des plannings des medecins.
- Gestion des rendez-vous.
- Gestion des consultations.
- Gestion des dossiers medicaux.
- Journal d'audit et statistiques.

## 7. Legende simple pour soutenance

- `Routes` : exposent les endpoints.
- `Controllers` : coordonnent les traitements.
- `Services` : portent les regles metier.
- `Repositories` : recuperent et manipulent les donnees.
- `Models` : representent les tables/metiers.
- `Policies` : controlent les permissions.
- `Events/Listeners` : gerent les notifications et actions decouplees.
- `Resources` : standardisent les reponses JSON.

## 8. Fichiers concrets a montrer avec le diagramme

- `routes/api.php`
- `app/Http/Controllers/Api/AdminController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/RendezVousController.php`
- `app/Services/Admin/CreateAdminService.php`
- `app/Services/Activation/ActivationService.php`
- `app/Services/RendezVous/CreateRendezVousService.php`
- `app/Repositories/RendezVousRepository.php`
- `app/Models/RendezVous.php`
- `app/Models/Patient.php`
- `app/Models/PlanningMedecin.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `resources/views/emails/admin-credentials.blade.php`
