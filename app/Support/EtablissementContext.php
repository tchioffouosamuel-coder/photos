<?php

namespace App\Support;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/*
 | Perimetre d'etablissement de la requete en cours.
 |
 | Deux notions distinctes, qu'il faut garder separees :
 |
 |   - la PORTEE du compte : a quels etablissements il a droit. Elle vient de
 |     users.etablissement_id et ne se negocie pas. Un compte rattache a un
 |     etablissement ne verra jamais les autres, quels que soient ses droits
 |     ou ce qu'il demande.
 |
 |   - l'etablissement ACTIF : lequel il regarde en ce moment. Cela ne
 |     concerne que les comptes a portee globale (etablissement_id null), qui
 |     ont acces a plusieurs etablissements et doivent pouvoir choisir. Le
 |     choix vit en session.
 |
 | Confondre les deux serait la faille evidente : si l'etablissement actif
 | suffisait a decider ce qu'on voit, un compte rattache a un etablissement
 | pourrait en selectionner un autre et lire les eleves du voisin. C'est
 | pourquoi definirActif() refuse tout identifiant hors de la portee, et
 | pourquoi idsAutorises() ne lit jamais la session.
 */
class EtablissementContext
{
    private const CLE_SESSION = 'etablissement_actif';

    /** Memorisation par requete : evite de reinterroger la base. */
    private ?Etablissement $actifResolu = null;

    private bool $actifCharge = false;

    /* ====================================================================
     | Portee du compte
     * ================================================================== */

    public function utilisateur(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * Le compte connecte voit-il tous les etablissements ?
     *
     * Hors contexte authentifie (commande artisan, migration, test) la
     * reponse est oui : rien ne doit etre masque a un traitement serveur.
     */
    public function aPorteeGlobale(): bool
    {
        $user = $this->utilisateur();

        return $user === null || $user->etablissement_id === null;
    }

    /**
     * Etablissements auxquels le compte a droit, ou null si tous.
     *
     * @return list<int>|null
     */
    public function idsAutorises(): ?array
    {
        $user = $this->utilisateur();

        if ($user === null || $user->etablissement_id === null) {
            return null;
        }

        return [(int) $user->etablissement_id];
    }

    /* ====================================================================
     | Etablissement actif
     * ================================================================== */

    /**
     * Identifiant de l'etablissement actif, ou null pour « tous ».
     */
    public function idActif(): ?int
    {
        $user = $this->utilisateur();

        if ($user === null) {
            return null;
        }

        /* Un compte rattache n'a pas de choix a faire : sa portee decide. */
        if ($user->etablissement_id !== null) {
            return (int) $user->etablissement_id;
        }

        $id = $this->depuisSession();

        return $id === null ? null : (int) $id;
    }

    public function actif(): ?Etablissement
    {
        if ($this->actifCharge) {
            return $this->actifResolu;
        }

        $this->actifCharge = true;

        $id = $this->idActif();

        $this->actifResolu = $id === null
            ? null
            : Etablissement::find($id);

        return $this->actifResolu;
    }

    /**
     * Change l'etablissement actif.
     *
     * @param  int|null  $id  null pour « tous les etablissements »
     * @return bool  false si l'identifiant est hors de la portee du compte
     */
    public function definirActif(?int $id): bool
    {
        if (! $this->aPorteeGlobale()) {
            /* Voir la note en tete de classe : la portee ne se choisit pas. */
            return false;
        }

        if ($id !== null && ! Etablissement::whereKey($id)->exists()) {
            return false;
        }

        $this->oublier();

        if (Session::isStarted()) {
            $id === null
                ? Session::forget(self::CLE_SESSION)
                : Session::put(self::CLE_SESSION, $id);
        }

        return true;
    }

    /* ====================================================================
     | Filtrage
     * ================================================================== */

    /**
     * Identifiants sur lesquels filtrer les requetes, ou null si aucun
     * filtre n'est a poser (portee globale sans selection).
     *
     * @return list<int>|null
     */
    public function idsEffectifs(): ?array
    {
        $autorises = $this->idsAutorises();

        if ($autorises !== null) {
            return $autorises;
        }

        $actif = $this->idActif();

        return $actif === null ? null : [$actif];
    }

    /**
     * Etablissement auquel rattacher une entite creee maintenant.
     *
     * Renvoie null quand le rattachement est ambigu : un compte a portee
     * globale qui regarde « tous les etablissements » et en a plusieurs sous
     * les yeux ne peut pas creer une classe sans dire laquelle. L'appelant
     * doit alors demander, pas deviner.
     */
    public function idPourCreation(): ?int
    {
        $ids = $this->idsEffectifs();

        if ($ids !== null && count($ids) === 1) {
            return $ids[0];
        }

        /* Un seul etablissement existe : aucune ambiguite possible. */
        $unique = Etablissement::query()->limit(2)->pluck('id');

        return $unique->count() === 1 ? (int) $unique->first() : null;
    }

    /**
     * Etablissements que le compte peut parcourir, pour un selecteur.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Etablissement>
     */
    public function disponibles()
    {
        $requete = Etablissement::query()->orderBy('nom');

        $autorises = $this->idsAutorises();

        if ($autorises !== null) {
            $requete->whereIn('id', $autorises);
        }

        return $requete->get();
    }

    /* ====================================================================
     | Outils
     * ================================================================== */

    /**
     * Oublie la resolution memorisee. Appele apres un changement
     * d'etablissement actif, et utile dans les tests.
     */
    public function oublier(): void
    {
        $this->actifResolu = null;
        $this->actifCharge = false;
    }

    private function depuisSession(): ?int
    {
        if (! Session::isStarted()) {
            return null;
        }

        $id = Session::get(self::CLE_SESSION);

        return is_numeric($id) ? (int) $id : null;
    }
}
