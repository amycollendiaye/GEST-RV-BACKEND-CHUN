<?php

namespace App\Providers;

use App\Events\AdminCreated;
use App\Events\ConsultationTerminee;
use App\Events\MedecinCreated;
use App\Events\PatientCreated;
use App\Events\RendezVousAttribue;
use App\Events\RendezVousReprogramme;
use App\Events\SecretaireCreated;
use App\Listeners\SendAdminCredentialsEmail;
use App\Listeners\SendConsultationTermineeSms;
use App\Listeners\SendMedecinCredentialsSms;
use App\Listeners\SendPatientCredentialsMail;
use App\Listeners\SendRendezVousAttribueSms;
use App\Listeners\SendRendezVousReprogrammeSms;
use App\Listeners\SendSecretaireCredentialsSms;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AdminCreated::class => [
            SendAdminCredentialsEmail::class,
        ],
        MedecinCreated::class => [
            SendMedecinCredentialsSms::class,
        ],
        SecretaireCreated::class => [
            SendSecretaireCredentialsSms::class,
        ],
        PatientCreated::class => [
            SendPatientCredentialsMail::class,
        ],
        RendezVousAttribue::class => [
            SendRendezVousAttribueSms::class,
        ],
        RendezVousReprogramme::class => [
            SendRendezVousReprogrammeSms::class,
        ],
        ConsultationTerminee::class => [
            SendConsultationTermineeSms::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
