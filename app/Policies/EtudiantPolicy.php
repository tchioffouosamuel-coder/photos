<?php

namespace App\Policies;

use App\Models\Etudiant;
use App\Models\User;
use App\Support\Permissions;

/*
 | Acces aux eleves.
 |
 | La portee est celle de la classe de l'eleve : un operateur photo assigne a
 | la 1ere ESF ne doit pas pouvoir ouvrir la fiche d'un eleve de la Tle F4.
 */
class EtudiantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_VOIR);
    }

    public function view(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_VOIR)
            && $this->dansLePerimetre($user, $etudiant);
    }

    public function create(User $user): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_CREER);
    }

    public function update(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_MODIFIER)
            && $this->dansLePerimetre($user, $etudiant);
    }

    public function delete(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_SUPPRIMER)
            && $this->dansLePerimetre($user, $etudiant);
    }

    public function transferer(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::ELEVES_TRANSFERER)
            && $this->dansLePerimetre($user, $etudiant);
    }

    /**
     * Prendre ou remplacer la photo d'un eleve.
     */
    public function gererPhoto(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::PHOTOS_PRENDRE)
            && $this->dansLePerimetre($user, $etudiant);
    }

    public function supprimerPhoto(User $user, Etudiant $etudiant): bool
    {
        return $user->aLaPermission(Permissions::PHOTOS_SUPPRIMER)
            && $this->dansLePerimetre($user, $etudiant);
    }

    /**
     * Un eleve sans classe reste accessible a qui n'est pas limite : sinon il
     * deviendrait impossible de le rattacher.
     */
    private function dansLePerimetre(User $user, Etudiant $etudiant): bool
    {
        if (! $user->estLimiteAuxClassesAssignees()) {
            return true;
        }

        $classe = $etudiant->classe;

        return $classe !== null && $user->peutAccederClasse($classe);
    }
}
