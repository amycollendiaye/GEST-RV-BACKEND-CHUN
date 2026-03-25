<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\RendezVous;
use App\Observers\ConsultationObserver;
use App\Observers\PatientObserver;
use App\Observers\PersonelHopitalObserver;
use App\Observers\RendezVousObserver;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use App\Repositories\Interfaces\MedecinRepositoryInterface;
use App\Repositories\Interfaces\PatientRepositoryInterface;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use App\Repositories\Interfaces\SecretaireRepositoryInterface;
use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;
use App\Repositories\AdminRepository;
use App\Repositories\ConsultationRepository;
use App\Repositories\DossierMedicalRepository;
use App\Repositories\JournalAuditRepository;
use App\Repositories\MedecinRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PlanningMedecinRepository;
use App\Repositories\RendezVousRepository;
use App\Repositories\SecretaireRepository;
use App\Repositories\ServiceMedicalRepository;
use App\Services\Interfaces\SmsNotificationInterface;
use App\Services\SmsNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(MedecinRepositoryInterface::class, MedecinRepository::class);
        $this->app->bind(SecretaireRepositoryInterface::class, SecretaireRepository::class);
        $this->app->bind(ServiceMedicalRepositoryInterface::class, ServiceMedicalRepository::class);
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PlanningMedecinRepositoryInterface::class, PlanningMedecinRepository::class);
        $this->app->bind(RendezVousRepositoryInterface::class, RendezVousRepository::class);
        $this->app->bind(DossierMedicalRepositoryInterface::class, DossierMedicalRepository::class);
        $this->app->bind(ConsultationRepositoryInterface::class, ConsultationRepository::class);
        $this->app->bind(JournalAuditRepositoryInterface::class, JournalAuditRepository::class);
        $this->app->bind(SmsNotificationInterface::class, SmsNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PersonelHopital::observe(PersonelHopitalObserver::class);
        Patient::observe(PatientObserver::class);
        RendezVous::observe(RendezVousObserver::class);
        Consultation::observe(ConsultationObserver::class);
    }
}
