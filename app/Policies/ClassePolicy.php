<?php

namespace App\Policies;

use App\Models\Classe;
use App\Models\User;
use App\Support\Permissions;

/*
 | Acces aux classes.
 |
 | Deux niveaux se combinent :
 |   - la permission, qui dit ce que le role a le droit de faire ;
 |   - la portee, qui dit sur quelles classes il peut le faire.
 |
 | Un operateur photo a bien la permission de consulter une classe, mais
 | seulement celles qui lui sont assignees. La permission sans la portee
 | laisserait un operateur parcourir tout l'etablissement.
 */
class ClassePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->aLaPermission(Permissions::CLASSES_VOIR);
    }

    public function view(User $user, Classe $classe): bool
    {
        return $user->aLaPermission(Permissions::CLASSES_VOIR)
            && $user->peutAccederClasse($classe);
    }

    public function create(User $user): bool
    {
        return $user->aLaPermission(Permissions::CLASSES_CREER);
    }

    public function update(User $user, Classe $classe): bool
    {
        return $user->aLaPermission(Permissions::CLASSES_MODIFIER)
            && $user->peutAccederClasse($classe);
    }

    public function delete(User $user, Classe $classe): bool
    {
        return $user->aLaPermission(Permissions::CLASSES_SUPPRIMER)
            && $user->peutAccederClasse($classe);
    }

    /**
     * Production de documents pour cette classe.
     */
    public function genererDocuments(User $user, Classe $classe): bool
    {
        return $user->aLaPermission(Permissions::DOCUMENTS_GENERER)
            && $user->peutAccederClasse($classe);
    }
}
