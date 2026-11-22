<?php

namespace App\Providers;

use App\Models\CandidatLibre;
use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Etudiant;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Policies\CandidatLibrePolicy;
use App\Policies\ClassePolicy;
use App\Policies\EtablissementPolicy;
use App\Policies\EtudiantPolicy;
use App\Policies\UserPolicy;
use App\Services\AuditLogger;
use App\Support\Permissions;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/*
 | Controle d'acces et journal d'activite.
 |
 | Regroupes ici plutot que disperses : les regles d'autorisation et les
 | traces qu'elles produisent se lisent ensemble.
 */
class AuthorizationServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private const POLITIQUES = [
        Classe::class => ClassePolicy::class,
        Etudiant::class => EtudiantPolicy::class,
        CandidatLibre::class => CandidatLibrePolicy::class,
        User::class => UserPolicy::class,
        Etablissement::class => EtablissementPolicy::class,
    ];

    public function boot(): void
    {
        $this->enregistrerPolitiques();
        $this->enregistrerPermissions();
        $this->enregistrerObservateurs();
        $this->enregistrerTracesAuthentification();
    }

    private function enregistrerPolitiques(): void
    {
        foreach (self::POLITIQUES as $modele => $politique) {
            Gate::policy($modele, $politique);
        }
    }

    /**
     * Chaque code de permission devient une porte utilisable telle quelle :
     *
     *     @can(Permissions::DOCUMENTS_GENERER_GLOBAL)
     *     ->middleware('can:' . Permissions::JOURNAL_CONSULTER)
     *
     * La liste vient du catalogue en PHP, pas de la base : aucune requete au
     * demarrage, et une permission inconnue reste refusee par defaut.
     */
    private function enregistrerPermissions(): void
    {
        /*
         | Pas de Gate::before pour l'administrateur.
         |
         | Un contournement global parait prudent, mais il court-circuite les
         | Policies — donc precisement les garde-fous qui empechent un
         | administrateur de supprimer son propre compte ou de retrograder le
         | dernier administrateur. Ces regles n'ont d'interet que pour cette
         | population : les neutraliser pour elle les vide de leur sens.
         |
         | Le filet existe quand meme, un cran plus bas :
         | User::aLaPermission() accorde tout a l'administrateur. Les portes
         | de permission restent donc toujours ouvertes pour lui, tandis que
         | les Policies gardent la main sur les cas particuliers.
         */
        foreach (Permissions::codes() as $code) {
            Gate::define($code, fn (User $user) => $user->aLaPermission($code));
        }
    }

    private function enregistrerObservateurs(): void
    {
        $observateur = AuditObserver::class;

        Classe::observe($observateur);
        Etudiant::observe($observateur);
        CandidatLibre::observe($observateur);
        User::observe($observateur);
        Etablissement::observe($observateur);
    }

    /**
     * Connexions, deconnexions et echecs.
     *
     * Les echecs sont traces sans l'identifiant saisi si celui-ci ne
     * correspond a aucun compte connu : enregistrer un mot de passe tape dans
     * le champ e-mail par erreur serait une fuite.
     */
    private function enregistrerTracesAuthentification(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $this->journal()->enregistrer(
                AuditLogger::CONNEXION,
                $event->user instanceof \Illuminate\Database\Eloquent\Model ? $event->user : null,
                null,
                null,
                $event->user->name ?? null
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (! $event->user) {
                return;
            }

            $this->journal()->enregistrer(
                AuditLogger::DECONNEXION,
                $event->user instanceof \Illuminate\Database\Eloquent\Model ? $event->user : null,
                null,
                null,
                $event->user->name ?? null
            );
        });

        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? null;
            $connu = $event->user !== null;

            $this->journal()->enregistrer(
                AuditLogger::CONNEXION_ECHOUEE,
                $event->user instanceof \Illuminate\Database\Eloquent\Model ? $event->user : null,
                null,
                ['email' => $connu ? $email : 'identifiant inconnu'],
                $connu ? $email : 'Tentative sur un compte inconnu'
            );
        });
    }

    private function journal(): AuditLogger
    {
        return $this->app->make(AuditLogger::class);
    }
}
