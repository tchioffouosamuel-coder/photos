<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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

        /*
         | Perimetre d'etablissement de la requete.
         |
         | scoped() et non singleton() : l'instance memorise l'etablissement
         | resolu, ce qui est juste le temps d'une requete et faux au-dela.
         | Sous un serveur persistant, un singleton servirait a la requete
         | suivante le perimetre de l'utilisateur precedent.
         */
        $this->app->scoped(\App\Support\EtablissementContext::class);
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

        /*
         | Les vues de pagination livrees par Laravel sont ecrites pour
         | Tailwind. L'application est batie sur Bootstrap, dont acid.css
         | habille deja .pagination et .page-link : sans ce reglage, les liens
         | de page s'afficheraient sans aucun style.
         */
        Paginator::useBootstrapFive();

        $this->sharePendingPhotosCount();
    }

    /**
     * Nombre d'eleves sans photo, pour la pastille d'alerte de la topbar et de
     * la navigation basse.
     *
     * Partage via un view composer limite aux deux partials concernes : la
     * requete n'est donc executee que si l'un d'eux est effectivement rendu,
     * et jamais sur les pages publiques ou d'authentification.
     */
    private function sharePendingPhotosCount(): void
    {
        View::composer(
            ['layouts.partials.topbar', 'layouts.partials.bottomnav'],
            function ($view) {
                static $compte = null;

                if ($compte === null) {
                    try {
                        $compte = Etudiant::where(
                            fn ($q) => $q->whereNull('photo')->orWhere('photo', '')
                        )->count();
                    } catch (\Throwable $e) {
                        /*
                         | Base indisponible ou migrations non jouees : la
                         | pastille n'est qu'un indicateur, elle ne doit jamais
                         | empecher l'affichage de la page.
                         */
                        $compte = 0;
                    }
                }

                $view->with('pendingPhotos', $compte);
            }
        );
    }
}