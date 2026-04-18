# Fonctionnalites phares a presenter en soutenance

Ce document t'aide a maitriser deux fonctionnalites fortes du systeme `GEST_RV_CHUN` :

1. le chiffrement des donnees sensibles
2. l'attribution automatique des rendez-vous

L'objectif est de disposer d'une explication technique solide, claire et defendable devant le jury.

## 1. Chiffrement des donnees : ce que fait reellement le systeme

### 1.1 Idee generale

Le systeme protege les donnees sensibles a plusieurs niveaux :

- les donnees personnelles et medicales sont chiffrees avant stockage en base
- les mots de passe ne sont pas stockes en clair, ils sont haches
- certaines colonnes disposent en plus d'un hash technique pour garantir l'unicite sans exposer la vraie valeur
- les champs les plus sensibles sont masques dans les logs applicatifs

Autrement dit, le systeme combine `confidentialite`, `integrite`, `non-exposition en base` et `protection des traces`.
-- definition des concepts :🔐 1. Confidentialité

La confidentialité consiste à garantir que seules les personnes autorisées peuvent accéder aux données.

 Objectif : empêcher l’accès non autorisé.



L’intégrité assure que les données ne sont pas modifiées ou altérées de manière non autorisée.

 Objectif : garantir que les données restent exactes et fiables.


 3. Non-exposition en base

La non-exposition en base signifie que les données sensibles ne doivent pas être stockées en clair dans la base de données.

 Objectif : protéger les données même en cas de fuite de la base.

 4. Protection des traces (logs)

La protection des traces concerne la sécurisation des journaux (logs) du système.

 Objectif : éviter que les logs révèlent des informations sensibles ou soient modifiés.



### 1.2 Difference essentielle a dire au jury

Il faut bien distinguer trois mecanismes :

- `Chiffrement` : permet de retrouver la valeur originale avec une cle secrete.
- `Hachage` : transformation irreversible, utile pour verifier ou imposer l'unicite.
- `Masquage` : empeche l'exposition accidentelle dans les logs.

Cette distinction est importante, car elle montre que l'architecture de securite n'est pas basee sur un seul mecanisme.

## 2. Processus complet de chiffrement

### 2.1 Ou se trouve la cle

La cle de chiffrement applicative n'est pas ecrite dans le code. Elle est chargee depuis la configuration via `ENCRYPTION_KEY`.

Fichiers de reference :

- `config/app.php`
- `app/Services/EncryptionService.php`

### 2.2 Algorithme utilise

Le service `EncryptionService` utilise `AES-256-GCM`.
AES-256-GCM = Une méthode qui rend tes données illisibles ET vérifie qu’elles n’ont pas été modifiées

Ce choix est fort pour une soutenance, car `AES-256-GCM` apporte :

- la confidentialite des donnees
- un tag d'authentification qui permet de detecter une alteration des donnees
- un IV aleatoire a chaque chiffrement
👉 AES (Advanced Encryption Standard) : C’est un algorithme de chiffrement
Utilisé partout : banques, WhatsApp, HTTPS…
🔢 2. 256: La clé est très longue (256 bits = 32 caractères)
Plus la clé est longue → plus c’est sécurisé

🛡️ 3. GCM : GCM = mode sécurisé intelligent

Il fait 2 choses :

Il chiffre (cache les données)
Il protège contre modification (grâce au TAG)


Concretement, le systeme :

1. recupere la cle depuis `ENCRYPTION_KEY`
2. verifie qu'elle fait bien `32 octets`
3. genere un `IV` aleatoire de `12 octets`
4. chiffre la valeur avec `AES-256-GCM`
5. concatene `IV + TAG + donnees chiffrees`
6. encode le tout en `base64` avant stockage

Donc, meme si deux utilisateurs ont la meme valeur sensible, le resultat stocke peut etre different grace a l'IV aleatoire.

### 2.3 Quand le chiffrement intervient

Le trait `EncryptableFields` automatise le mecanisme.

Avant sauvegarde :

- lors d'un `saving`, chaque champ declare dans `$encryptable` est chiffre
- le systeme verifie d'abord que la valeur n'est pas deja chiffree

Apres lecture :

- lors d'un `retrieved`, les champs sont dechiffres pour etre reutilisables dans l'application
- l'accesseur `getAttribute()` dechiffre aussi a la volee

Cela signifie que le developpeur travaille en general avec des valeurs lisibles dans le code, tandis que la base stocke des valeurs protegees.

Fichier central :

- `app/Traits/EncryptableFields.php`

## 3. Quelles donnees sont chiffrees

### 3.1 Donnees patient

Le modele `Patient` chiffre notamment :

- `nom`
- `prenom`
- `email`
- `telephone`
- `adresse`

### 3.2 Donnees personnel hospitalier

Le modele `PersonelHopital` chiffre :

- `nom`
- `prenom`
- `email`
- `telephone`
- `specialite`

### 3.3 Donnees medicales

Le modele `DossierMedical` chiffre les antecedents et informations cliniques sensibles :

- antecedents medicaux
- antecedents chirurgicaux
- antecedents familiaux
- allergies
- maladies chroniques
- traitements en cours

### 3.4 Donnees de service

Le modele `ServiceMedical` chiffre :

- `nom`
- `description`

Fichiers de reference :

- `app/Models/Patient.php`
- `app/Models/PersonelHopital.php`
- `app/Models/DossierMedical.php`
- `app/Models/ServiceMedical.php`

## 4. Pourquoi il y a aussi des colonnes hash

Le chiffrement protege la confidentialite, mais il est peu pratique pour verifier l'unicite de certaines donnees.

Le systeme ajoute donc des colonnes techniques :

- `email_hash`
- `telephone_hash`
- `nom_hash`

Ces colonnes sont calculees avec `SHA-256` a partir des valeurs normalisees.

Elles servent a :

- empecher les doublons d'email
- empecher les doublons de telephone
- empecher les doublons de nom de service
- conserver des index uniques sans stocker la vraie valeur en clair

Message fort a dire :

`Le systeme combine chiffrement pour la confidentialite et hachage pour l'unicite et les controles metier.`

## 5. Cas particulier des mots de passe

Les mots de passe ne sont pas chiffres, ils sont `haches`.

C'est une bonne pratique de securite :

- on ne doit pas pouvoir reconstituer un mot de passe original
- la verification se fait avec `Hash::check()`
- lors de l'activation et du changement de mot de passe, la valeur est enregistree avec `Hash::make()`

Dans le systeme :

- pour le personnel, le mot de passe est stocke dans `infos_connexions`
- pour le patient, il est stocke sur le modele `Patient`

Fichiers de reference :

- `app/Services/Auth/LoginService.php`
- `app/Services/Activation/ActivationService.php`
- `app/Models/InfosConnexion.php`

## 6. Protection des logs : point subtil mais important

Le middleware `EncryptRequestData` ne chiffre pas la requete HTTP.

Son role reel est surtout de :

- masquer les champs comme `password`
- eviter qu'une donnee critique apparaisse telle quelle dans les logs Laravel

Donc, devant le jury, il faut dire exactement ceci :

`Au niveau applicatif, les donnees sensibles sont chiffrees avant stockage. Le middleware complete cette protection en masquant les informations critiques dans les traces applicatives.`

Et il faut eviter de dire :

`Toutes les requetes HTTP sont chiffrees par ce middleware.`

Ce serait techniquement faux. Le chiffrement du transport releve plutot de `HTTPS/TLS`.

## 7. Ce qui rend ce mecanisme convaincant en soutenance

### 7.1 Valeur technique

- les donnees sensibles ne sont pas stockees en clair
- la cle reste hors du code source
- un IV aleatoire renforce la securite
- le tag GCM protege contre l'alteration
- l'unicite metier reste possible grace aux colonnes hash
- les mots de passe suivent une logique differente, conforme aux bonnes pratiques

### 7.2 Valeur metier

Dans un contexte hospitalier, cette approche permet de proteger :

- l'identite des patients
- les contacts du personnel
- les informations medicales confidentielles
- la confiance dans le systeme

## 8. Formulation orale recommandee pour impressionner le jury

Tu peux dire :

`Nous avons mis en place une securisation multicouche des donnees. Les informations sensibles, comme l'identite du patient ou les antecedents medicaux, sont chiffrees au moment de la sauvegarde avec AES-256-GCM. Les mots de passe, eux, ne sont pas chiffrables mais haches pour rester irreversibles. En complement, nous utilisons des hash techniques pour garantir l'unicite des emails, des telephones et des services sans exposer les vraies valeurs en base. Enfin, nous masquons les champs critiques dans les logs afin d'eviter toute fuite accidentelle dans les traces applicatives.`  

## 9. Attribution automatique des rendez-vous : idee generale

Le systeme ne se contente pas d'enregistrer un rendez-vous. Il `attribue automatiquement` au patient :

- le bon service
- le planning le plus proche disponible
- un medecin actif de ce service
- une heure precise calculee selon la capacite du planning

Cette fonctionnalite est forte, car elle automatise une tache organisationnelle complexe avec des contraintes metier.

## 10. Processus complet d'attribution automatique

### 10.1 Point d'entree

Le patient envoie une requete `POST /api/rendez-vous` avec :

- `service_medical_id`
- `motif`

Le controller :

- valide les donnees
- verifie le droit `rendezvous.create`
- verifie que l'utilisateur connecte est bien un `Patient`
- appelle le service d'attribution automatique

Fichiers de reference :

- `app/Http/Controllers/Api/AttributionRendezVousController.php`
- `app/Http/Requests/RendezVous/AttributionRendezVousRequest.php`

### 10.2 Encapsulation dans une transaction

L'attribution se fait dans une transaction base de donnees.

Interet :

- si une etape echoue, rien n'est partiellement enregistre
- on evite les incoherences entre planning, rendez-vous et notification

Autre point mature :

- l'evenement de notification n'est declenche qu'apres `commit`
- donc le patient ne recoit pas un SMS pour un rendez-vous qui aurait ete annule par rollback

### 10.3 Verifications metier avant attribution

Le systeme execute deux controles avant toute affectation :

1. `VerifierContrainteServiceService`
   Le patient ne doit pas deja avoir un rendez-vous `PLANIFIER` dans le meme service.

2. `VerifierConsultationPrecedenteService`
   Le patient ne doit pas avoir un parcours precedent non termine dans ce meme service.

Cela evite :

- les doublons de rendez-vous
- les engorgements
- les consultations de suivi creees sans cloture du precedent cycle

### 10.4 Recherche du meilleur planning

Le repository cherche le `planning le plus proche disponible` pour le service demande.

Conditions appliquees :

- le planning appartient au service cible
- la date du planning est future
- le medecin lie au planning est `ACTIF`
- le medecin appartient bien a ce service
- le nombre de rendez-vous deja attribues est inferieur a la capacite du planning
- les rendez-vous annules ne consomment pas la capacite

Puis le systeme trie par :

- date croissante
- heure d'ouverture croissante

Resultat :

- on selectionne le premier planning valable, donc le plus proche dans le temps

### 10.5 Calcul automatique de l'heure du rendez-vous

Une fois le planning choisi, l'heure n'est pas saisie manuellement.

Le systeme calcule :

1. la duree totale disponible entre `heure_ouverture` et `heure_fermeture`
2. la duree moyenne par patient selon `capacite`
3. le rang du patient dans ce planning selon le nombre de rendez-vous deja attribues
4. l'heure exacte en ajoutant `rang x duree_par_patient` a l'heure d'ouverture

Exemple simple :

- ouverture : `08:00`
- fermeture : `12:00`
- duree totale : `240 minutes`
- capacite : `8`
- duree par patient : `30 minutes`

Donc :

- 1er patient : `08:00`
- 2e patient : `08:30`
- 3e patient : `09:00`

Ce mecanisme montre une logique algorithmique concrete et facile a expliquer au jury.

### 10.6 Creation du rendez-vous

Le rendez-vous cree contient :

- l'identifiant du patient
- l'identifiant du service
- l'identifiant du medecin retenu
- l'identifiant du planning retenu
- la date et l'heure calculees
- le motif
- le statut initial `PLANIFIER`

### 10.7 Notification automatique

Apres validation finale en base :

- un evenement `RendezVousAttribue` est emis
- un listener `SendRendezVousAttribueSms` est declenche
- le service `NotifierRendezVousService` construit le message
- le patient recoit un SMS avec la date, l'heure, le service et le medecin

Ici, l'architecture est interessante car la notification est `decouplee` de la logique centrale.

Autrement dit :

- le service d'attribution gere la decision metier
- l'evenement gere la diffusion
- le listener gere l'action de notification

Cette separation est elegante et tres defendable en soutenance.

## 11. Ce qui rend cette attribution intelligente

- elle est basee sur des contraintes metier reelles
- elle evite les doublons et les conflits
- elle s'appuie sur la disponibilite effective des medecins
- elle respecte la capacite du planning
- elle calcule automatiquement un horaire coherent
- elle declenche une notification sans coupler fortement les composants

## 12. Formulation orale recommandee pour le jury

Tu peux dire :

`L'une des fonctionnalites phares du systeme est l'attribution automatique des rendez-vous. Lorsqu'un patient formule une demande, le systeme ne fait pas qu'enregistrer cette demande. Il verifie d'abord qu'il n'existe ni doublon ni parcours non termine dans le meme service. Ensuite, il recherche automatiquement le planning futur le plus proche dont la capacite n'est pas encore saturee et dont le medecin est actif dans le service concerne. Enfin, il calcule l'heure exacte du rendez-vous en fonction de la plage horaire et de la capacite du planning. Une fois le rendez-vous confirme en base, une notification SMS est envoyee au patient. Cette approche nous a permis d'automatiser l'organisation tout en respectant les contraintes metier du milieu hospitalier.`  

## 13. Arguments forts a sortir si le jury creuse

### 13.1 Sur la securite

- `Nous n'avons pas seulement masque des champs, nous avons separe chiffrement, hachage et masquage selon leur usage.`
- `Les donnees medicales restent non lisibles en base sans la cle applicative.`
- `Les mots de passe suivent un traitement irreversible, ce qui est plus approprie que le chiffrement.`

### 13.2 Sur le rendez-vous

- `La logique d'attribution est transactionnelle, donc coherente meme en cas d'echec.`
- `La notification est declenchee apres validation de la transaction, ce qui evite les faux SMS.`
- `Le calcul du creneau repose sur une logique de capacite, donc le planning est structure plutot que purement manuel.`

## 14. Questions probables du jury et reponses courtes

### Question : pourquoi ne pas stocker les donnees en clair si l'application les dechiffre ensuite ?

Reponse :

`Parce qu'en cas d'acces direct a la base de donnees, les informations resteraient inexploitable sans la cle de chiffrement.`

### Question : pourquoi utiliser aussi des hash si les donnees sont deja chiffrees ?

Reponse :

`Le chiffrement protege la confidentialite, alors que le hash permet les controles d'unicite et certains controles metier sans exposer la valeur originale.`

### Question : comment evitez-vous de surcharger un planning de medecin ?

Reponse :

`Avant l'attribution, le systeme compare le nombre de rendez-vous actifs deja affectes a la capacite du planning. Si la capacite est atteinte, le planning est exclu.`

### Question : pourquoi parler d'evenements et de listeners ?

Reponse :

`Parce que cela permet de separer la logique de decision metier de la logique de notification, ce qui rend le systeme plus modulaire et evolutif.`

## 15. Points de vigilance a presenter avec honnetete

- Le chiffrement applicatif protege surtout les donnees au repos dans la base.
- Le chiffrement du transport reseau releve plutot de `HTTPS/TLS`.
- Le middleware `EncryptRequestData` sert surtout au masquage des logs.
- La qualite de la protection depend aussi de la bonne gestion de `ENCRYPTION_KEY`.

Ces precisions renforcent ta credibilite devant le jury, car elles montrent que tu maitrises les limites du systeme autant que ses points forts.

## 16. Fichiers a citer pendant la soutenance

- `app/Services/EncryptionService.php`
- `app/Traits/EncryptableFields.php`
- `app/Models/Patient.php`
- `app/Models/PersonelHopital.php`
- `app/Models/DossierMedical.php`
- `app/Models/ServiceMedical.php`
- `app/Http/Middleware/EncryptRequestData.php`
- `app/Http/Controllers/Api/AttributionRendezVousController.php`
- `app/Http/Requests/RendezVous/AttributionRendezVousRequest.php`
- `app/Services/RendezVous/AttributionAutomatiqueRendezVousService.php`
- `app/Services/RendezVous/VerifierContrainteServiceService.php`
- `app/Services/RendezVous/VerifierConsultationPrecedenteService.php`
- `app/Repositories/PlanningMedecinRepository.php`
- `app/Repositories/RendezVousRepository.php`
- `app/Events/RendezVousAttribue.php`
- `app/Listeners/SendRendezVousAttribueSms.php`
- `app/Services/RendezVous/NotifierRendezVousService.php`
