<?php

namespace App\Policies;

use App\Models\CandidatLibre;
use App\Models\User;
use App\Support\Permissions;

/*
 | Acces aux candidats libres. La portee suit celle de la classe de
 | rattachement, comme pour les eleves.
 */
class CandidatLibrePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->aLaPermission(Permissions::CANDIDATS_VOIR);
    }

    public function view(User $user, CandidatLibre $candidat): bool
    {
        return $user->aLaPermission(Permissions::CANDIDATS_VOIR)
            && $this->dansLePerimetre($user, $candidat);
    }

    public function create(User $user): bool
    {
        return $user->aLaPermission(Permissions::CANDIDATS_CREER);
    }

    public function update(User $user, CandidatLibre $candidat): bool
    {
        return $user->aLaPermission(Permissions::CANDIDATS_MODIFIER)
            && $this->dansLePerimetre($user, $candidat);
    }

    public function delete(User $user, CandidatLibre $candidat): bool
    {
        return $user->aLaPermission(Permissions::CANDIDATS_SUPPRIMER)
            && $this->dansLePerimetre($user, $candidat);
    }

    public function gererPhoto(User $user, CandidatLibre $candidat): bool
    {
        return $user->aLaPermission(Permissions::PHOTOS_PRENDRE)
            && $this->dansLePerimetre($user, $candidat);
    }

    private function dansLePerimetre(User $user, CandidatLibre $candidat): bool
    {
        if (! $user->estLimiteAuxClassesAssignees()) {
            return true;
        }

        $classe = $candidat->classe;

        return $classe !== null && $user->peutAccederClasse($classe);
    }
}
