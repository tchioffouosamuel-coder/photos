<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/*
 | Restreint une requete aux classes visibles par un compte.
 |
 | Les Policies protegent l'acces a UN objet designe ; elles ne filtrent pas
 | les listes. Sans ce filtre, un operateur photo assigne a une seule classe
 | verrait quand meme tout l'etablissement sur le tableau de bord, et se
 | heurterait a un refus seulement au moment d'ouvrir une fiche.
 |
 | La classe qui l'utilise fournit colonneClasse() : « id » pour Classe,
 | « classe_id » pour Etudiant et CandidatLibre.
 */
trait PorteeParClasse
{
    public function scopeVisiblesPar(Builder $query, ?User $user = null): Builder
    {
        $user ??= Auth::user();

        if (! $user) {
            return $query;
        }

        $ids = $user->classesVisibles();

        /* null : le compte voit tout, aucune restriction a poser. */
        if ($ids === null) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->getTable() . '.' . static::colonneClasse(),
            $ids
        );
    }

    /**
     * Colonne portant l'identifiant de classe sur ce modele.
     */
    public static function colonneClasse(): string
    {
        return 'classe_id';
    }
}
