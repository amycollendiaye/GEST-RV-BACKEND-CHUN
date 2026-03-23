<?php

namespace App\Providers;

use App\Models\ServiceMedical;
use App\Policies\MedecinPolicy;
use App\Policies\SecretairePolicy;
use App\Policies\ServiceMedicalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
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
    }
}
