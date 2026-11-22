<?php

namespace App\Policies;

use App\Models\Etablissement;
use App\Models\User;
use App\Support\Permissions;

/*
 | Acces aux etablissements.
 |
 | Le garde-fou essentiel est ici : creer ou supprimer un etablissement exige
 | la PORTEE GLOBALE, pas seulement la permission.
 |
 | Sans cela, confier l'administration d'une ecole a son directeur
 | informatique lui donnerait aussi le droit de creer des etablissements, de
 | s'y rattacher et d'en sortir — autrement dit de defaire son propre
 | cloisonnement. La permission dit ce qu'on sait faire ; la portee dit sur
 | quoi. Pour les etablissements eux-memes, les deux sont necessaires.
 |
 | Consequence a assumer : un administrateur rattache a un etablissement
 | administre son etablissement entierement (comptes, classes, documents)
 | mais ne peut pas toucher a la liste des etablissements. C'est le but.
 */
class EtablissementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->aLaPermission(Permissions::ETABLISSEMENTS_VOIR);
    }

    public function view(User $user, Etablissement $etablissement): bool
    {
        return $user->aLaPermission(Permissions::ETABLISSEMENTS_VOIR)
            && $user->peutAccederEtablissement($etablissement);
    }

    public function create(User $user): bool
    {
        return $user->aLaPermission(Permissions::ETABLISSEMENTS_GERER)
            && $user->aPorteeGlobale();
    }

    public function update(User $user, Etablissement $etablissement): bool
    {
        return $user->aLaPermission(Permissions::ETABLISSEMENTS_GERER)
            && $user->peutAccederEtablissement($etablissement);
    }

    public function delete(User $user, Etablissement $etablissement): bool
    {
        if (! $user->aLaPermission(Permissions::ETABLISSEMENTS_GERER) || ! $user->aPorteeGlobale()) {
            return false;
        }

        /*
         | Le dernier etablissement ne se supprime pas : l'application
         | n'aurait plus ou ranger quoi que ce soit, et la prochaine classe
         | creee se retrouverait sans rattachement.
         */
        if (Etablissement::query()->count() <= 1) {
            return false;
        }

        /*
         | Un etablissement qui contient encore des donnees ne se supprime
         | pas non plus. La contrainte de cle etrangere le refuserait de
         | toute facon, mais avec une erreur de base illisible plutot qu'un
         | message : autant repondre ici.
         */
        return ! $etablissement->aDesDonnees();
    }

    /**
     * Basculer vers un autre etablissement.
     */
    public function basculer(User $user, Etablissement $etablissement): bool
    {
        return $user->aPorteeGlobale()
            && $user->aLaPermission(Permissions::ETABLISSEMENTS_VOIR);
    }
}
