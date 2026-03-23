<?php

namespace App\Providers;

use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\MedecinRepositoryInterface;
use App\Repositories\Interfaces\SecretaireRepositoryInterface;
use App\Repositories\Interfaces\ServiceMedicalRepositoryInterface;
use App\Repositories\AdminRepository;
use App\Repositories\MedecinRepository;
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
        $this->app->bind(SmsNotificationInterface::class, SmsNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
