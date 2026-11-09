<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Etudiant;
use App\Observers\EtudiantObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les services
        $this->app->singleton(\App\Services\ExcelImportService::class, function ($app) {
            return new \App\Services\ExcelImportService();
        });
        
        $this->app->singleton(\App\Services\CardGenerationService::class, function ($app) {
            return new \App\Services\CardGenerationService();
        });
        
        $this->app->singleton(\App\Services\PhotoProcessingService::class, function ($app) {
            return new \App\Services\PhotoProcessingService();
        });
        
        $this->app->singleton(\App\Services\Photo4x4Service::class, function ($app) {
            return new \App\Services\Photo4x4Service();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer pour mettre à jour les effectifs automatiquement
        Etudiant::observe(EtudiantObserver::class);
        
        if (str_contains(url('/'), '/public')) {
            $this->app['url']->forceRootUrl(str_replace('/public', '', url('/')));
        }
    }
}