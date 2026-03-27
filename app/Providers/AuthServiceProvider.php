<?php

namespace App\Providers;

use App\Models\JournalAudit;
use App\Models\PersonelHopital;
use App\Models\ServiceMedical;
use App\Policies\ConsultationPolicy;
use App\Policies\DossierMedicalPolicy;
use App\Policies\JournalAuditPolicy;
use App\Policies\MePolicy;
use App\Policies\MedecinPolicy;
use App\Policies\PatientPolicy;
use App\Policies\PlanningMedecinPolicy;
use App\Policies\RendezVousPolicy;
use App\Policies\SecretairePolicy;
use App\Policies\ServiceMedicalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ServiceMedical::class => ServiceMedicalPolicy::class,
        JournalAudit::class => JournalAuditPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('medecin.viewAny', [MedecinPolicy::class, 'viewAny']);
        Gate::define('medecin.view', [MedecinPolicy::class, 'view']);
        Gate::define('medecin.create', [MedecinPolicy::class, 'create']);
        Gate::define('medecin.update', [MedecinPolicy::class, 'update']);
        Gate::define('medecin.delete', [MedecinPolicy::class, 'delete']);
        Gate::define('medecin.changerStatut', [MedecinPolicy::class, 'changerStatut']);

        Gate::define('secretaire.viewAny', [SecretairePolicy::class, 'viewAny']);
        Gate::define('secretaire.view', [SecretairePolicy::class, 'view']);
        Gate::define('secretaire.create', [SecretairePolicy::class, 'create']);
        Gate::define('secretaire.update', [SecretairePolicy::class, 'update']);
        Gate::define('secretaire.delete', [SecretairePolicy::class, 'delete']);
        Gate::define('secretaire.changerStatut', [SecretairePolicy::class, 'changerStatut']);

        Gate::define('service.viewAny', [ServiceMedicalPolicy::class, 'viewAny']);
        Gate::define('service.view', [ServiceMedicalPolicy::class, 'view']);
        Gate::define('service.create', [ServiceMedicalPolicy::class, 'create']);
        Gate::define('service.update', [ServiceMedicalPolicy::class, 'update']);
        Gate::define('service.delete', [ServiceMedicalPolicy::class, 'delete']);

        Gate::define('patient.viewAny', [PatientPolicy::class, 'viewAny']);
        Gate::define('patient.view', [PatientPolicy::class, 'view']);
        Gate::define('patient.create', [PatientPolicy::class, 'create']);
        Gate::define('patient.update', [PatientPolicy::class, 'update']);
        Gate::define('patient.delete', [PatientPolicy::class, 'delete']);

        Gate::define('planning.viewAny', [PlanningMedecinPolicy::class, 'viewAny']);
        Gate::define('planning.view', [PlanningMedecinPolicy::class, 'view']);
        Gate::define('planning.create', [PlanningMedecinPolicy::class, 'create']);
        Gate::define('planning.update', [PlanningMedecinPolicy::class, 'update']);
        Gate::define('planning.delete', [PlanningMedecinPolicy::class, 'delete']);

        Gate::define('rendezvous.viewAny', [RendezVousPolicy::class, 'viewAny']);
        Gate::define('rendezvous.view', [RendezVousPolicy::class, 'view']);
        Gate::define('rendezvous.create', [RendezVousPolicy::class, 'create']);
        Gate::define('rendezvous.annuler', [RendezVousPolicy::class, 'annuler']);
        Gate::define('rendezvous.changerStatut', [RendezVousPolicy::class, 'changerStatut']);

        Gate::define('dossier.view', [DossierMedicalPolicy::class, 'view']);
        Gate::define('dossier.update', [DossierMedicalPolicy::class, 'update']);

        Gate::define('consultation.viewAny', [ConsultationPolicy::class, 'viewAny']);
        Gate::define('consultation.view', [ConsultationPolicy::class, 'view']);
        Gate::define('consultation.create', [ConsultationPolicy::class, 'create']);
        Gate::define('consultation.update', [ConsultationPolicy::class, 'update']);
        Gate::define('consultation.cloturer', [ConsultationPolicy::class, 'cloturer']);
        Gate::define('consultation.reprogrammer', [ConsultationPolicy::class, 'reprogrammer']);

        Gate::define('journal.viewAny', [JournalAuditPolicy::class, 'viewAny']);
        Gate::define('journal.view', [JournalAuditPolicy::class, 'view']);
        Gate::define('journal.export', [JournalAuditPolicy::class, 'export']);
        Gate::define('me.viewAdmin', [MePolicy::class, 'viewAdmin']);
        Gate::define('me.viewMedecin', [MePolicy::class, 'viewMedecin']);
        Gate::define('me.viewSecretaire', [MePolicy::class, 'viewSecretaire']);
        Gate::define('me.viewPatient', [MePolicy::class, 'viewPatient']);

        Gate::define('statistiques.admin', function (PersonelHopital $user): Response {
            return $user->role === 'ADMIN'
                ? Response::allow()
                : Response::deny('Seul l\'administrateur peut accéder à ce tableau de bord.');
        });

        Gate::define('statistiques.medecin', function (PersonelHopital $user): Response {
            return $user->role === 'MEDECIN'
                ? Response::allow()
                : Response::deny('Seul un médecin peut accéder à ce tableau de bord.');
        });

        Gate::define('statistiques.secretaire', function (PersonelHopital $user): Response {
            return $user->role === 'SECRETAIRE'
                ? Response::allow()
                : Response::deny('Seule une secrétaire peut accéder à ce tableau de bord.');
        });
    }
}
