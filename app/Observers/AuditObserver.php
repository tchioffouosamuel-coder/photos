<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/*
 | Branche le journal sur le cycle de vie des entites metier.
 |
 | L'observateur est enregistre pour Classe, Etudiant, CandidatLibre et User :
 | toute creation, modification ou suppression laisse une trace sans que les
 | controleurs aient a y penser. Les evenements qui ne sont pas un changement
 | d'entite (photo prise, document genere, connexion) sont traces explicitement
 | la ou ils se produisent, car eux seuls connaissent leur contexte.
 */
class AuditObserver
{
    public function __construct(private readonly AuditLogger $journal)
    {
    }

    public function created(Model $model): void
    {
        $this->journal->creation($model);
    }

    /**
     * On ecrit AVANT la sauvegarde : getDirty() et getOriginal() ne donnent
     * l'ecart que tant que la mise a jour n'est pas appliquee.
     */
    public function updating(Model $model): void
    {
        $this->journal->modification($model);
    }

    public function deleting(Model $model): void
    {
        $this->journal->suppression($model);
    }
}
