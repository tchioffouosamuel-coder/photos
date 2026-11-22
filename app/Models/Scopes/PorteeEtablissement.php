<?php

namespace App\Models\Scopes;

use App\Support\EtablissementContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/*
 | Cloisonnement par etablissement, applique a TOUTES les requetes.
 |
 | C'est volontairement une portee globale et non un filtre a appeler, a la
 | difference de visiblesPar() qui gere les classes assignees a un operateur.
 |
 | La raison est la nature de l'erreur possible. Oublier visiblesPar() montre
 | a un operateur photo des classes de son propre etablissement : c'est genant
 | mais interne. Oublier un filtre d'etablissement montre a un etablissement
 | les eleves d'un autre : c'est une fuite de donnees personnelles entre deux
 | organisations distinctes. Une protection dont l'oubli coute aussi cher ne
 | doit pas dependre du fait que chaque requete pense a l'appeler.
 |
 | Elle se retire explicitement quand c'est necessaire :
 |     Classe::withoutGlobalScope(PorteeEtablissement::class)
 | ce qui rend l'intention visible a la relecture.
 */
class PorteeEtablissement implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $ids = app(EtablissementContext::class)->idsEffectifs();

        /* null : aucun perimetre a imposer (portee globale, console, test). */
        if ($ids === null) {
            return;
        }

        $builder->whereIn(
            $model->getTable() . '.etablissement_id',
            $ids
        );
    }
}
