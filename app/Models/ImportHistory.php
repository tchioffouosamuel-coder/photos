<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Model;

/*
 | Historique des imports de listes.
 |
 | Cloisonne comme le reste : l'historique porte des noms de fichiers et de
 | classes, qui en disent long sur un etablissement. Sans rattachement, la
 | page d'import afficherait a chacun les imports de tous les autres.
 */
class ImportHistory extends Model
{
    use AppartientAEtablissement;

    protected $table = 'import_history';
    protected $fillable = [
        'classe_id', 'file_name', 'total_rows', 'imported_rows',
        'failed_rows', 'status', 'error_log', 'imported_by'
    ];

    protected $casts = [
        'error_log' => 'array'
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function etablissementDoitEtreResolu(): bool
    {
        return $this->etablissement_id === null || $this->isDirty('classe_id');
    }

    /**
     * Le rattachement vient de la classe importee, source autoritaire.
     */
    public function resoudreEtablissementId(): ?int
    {
        if ($this->classe_id === null) {
            return $this->etablissement_id === null ? null : (int) $this->etablissement_id;
        }

        $id = Classe::withoutGlobalScopes()
            ->whereKey($this->classe_id)
            ->value('etablissement_id');

        return $id === null ? null : (int) $id;
    }
}
