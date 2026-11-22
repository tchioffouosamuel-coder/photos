<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;

/*
 | Gestion des comptes.
 |
 | Trois garde-fous, au-dela de la simple permission. Ils existent pour qu'une
 | manoeuvre ordinaire ne puisse pas rendre l'application inadministrable :
 |
 |   - on ne supprime pas son propre compte depuis l'administration ;
 |   - on ne retire pas son propre role d'administrateur ;
 |   - on ne supprime ni ne retrograde le DERNIER administrateur.
 |
 | Sans le troisieme, il suffirait de retrograder l'unique administrateur pour
 | qu'il ne reste plus personne capable de gerer les comptes.
 |
 | S'y ajoute, depuis le multi-etablissement, un quatrieme garde-fou : on
 | n'agit que sur les comptes de son propre perimetre.
 |
 | Il merite une explication, car c'est le point par lequel tout le
 | cloisonnement pourrait s'effondrer. Les classes et les eleves sont
 | cloisonnes par une portee globale sur leurs requetes ; les COMPTES ne le
 | sont pas, parce qu'un compte a portee globale n'appartient a aucun
 | etablissement et doit rester administrable. Le cloisonnement des comptes
 | repose donc entierement sur cette Policy.
 |
 | Et ce qui est en jeu n'est pas la lecture d'une fiche : qui peut modifier
 | un compte peut en changer le rattachement, donc se donner acces a
 | l'etablissement de son choix. Un administrateur rattache a une ecole qui
 | pourrait editer les comptes d'une autre pourrait s'y rattacher lui-meme.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER);
    }

    public function view(User $user, User $cible): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER)
            && $this->memePerimetre($user, $cible);
    }

    public function create(User $user): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER);
    }

    public function update(User $user, User $cible): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER)
            && $this->memePerimetre($user, $cible);
    }

    /**
     * Changer le role d'un compte.
     */
    public function changerRole(User $user, User $cible): bool
    {
        if (! $user->aLaPermission(Permissions::COMPTES_GERER) || ! $this->memePerimetre($user, $cible)) {
            return false;
        }

        if ($user->is($cible)) {
            return false;   /* on ne modifie pas son propre niveau d'acces */
        }

        if ($cible->estAdministrateur() && $this->estLeDernierAdministrateur($cible)) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $cible): bool
    {
        if (! $user->aLaPermission(Permissions::COMPTES_GERER) || ! $this->memePerimetre($user, $cible)) {
            return false;
        }

        if ($user->is($cible)) {
            return false;
        }

        if ($cible->estAdministrateur() && $this->estLeDernierAdministrateur($cible)) {
            return false;
        }

        return true;
    }

    /**
     * Assigner des classes a un compte.
     */
    public function assignerClasses(User $user, User $cible): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER)
            && $this->memePerimetre($user, $cible);
    }

    /**
     * Rattacher un compte a un autre etablissement.
     *
     * Reserve a la portee globale. Sans cela, un administrateur d'ecole
     * deplacerait un compte — le sien compris — vers l'etablissement de son
     * choix, et le cloisonnement ne tiendrait plus.
     */
    public function changerEtablissement(User $user, User $cible): bool
    {
        return $user->aLaPermission(Permissions::COMPTES_GERER)
            && $user->aPorteeGlobale();
    }

    /**
     * Le compte cible est-il dans le perimetre de celui qui agit ?
     *
     * Un compte a portee globale administre tout le monde. Un compte
     * rattache n'administre que les comptes du meme etablissement — et pas
     * les comptes a portee globale, qui lui sont superieurs : sans cette
     * derniere exclusion, un administrateur d'ecole pourrait supprimer
     * l'administrateur du reseau.
     */
    private function memePerimetre(User $user, User $cible): bool
    {
        if ($user->aPorteeGlobale()) {
            return true;
        }

        if ($cible->aPorteeGlobale()) {
            return false;
        }

        return (int) $user->etablissement_id === (int) $cible->etablissement_id;
    }

    private function estLeDernierAdministrateur(User $cible): bool
    {
        if (! $cible->role_id) {
            return false;
        }

        return User::where('role_id', $cible->role_id)
            ->whereKeyNot($cible->getKey())
            ->doesntExist();
    }
}
