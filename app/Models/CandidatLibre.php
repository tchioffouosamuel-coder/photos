<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use App\Models\Concerns\PorteeParClasse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatLibre extends Model
{
    use AppartientAEtablissement, PorteeParClasse;

    /*
     | Table declaree explicitement : la convention Laravel deduirait
     | "candidat_libres" du nom de classe, alors que la migration cree
     | "candidats_libres". Voir la migration de consolidation
     | 2026_11_09_000000_consolidate_candidats_libres_table.
     */
    protected $table = 'candidats_libres';

    protected $fillable = [
        'classe_id',
        'nom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'photo'
    ];

    protected $casts = [
        'date_naissance' => 'date'
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Le rattachement est tenu de la CLASSE, qui en est la source
     * autoritaire : un eleve appartient a l'etablissement de sa classe, et
     * a aucun autre. Le deduire du perimetre de la requete permettrait a un
     * compte a portee globale de creer un eleve dans la classe d'un
     * etablissement et de le rattacher a un autre.
     */
    /**
     * Un changement de classe peut etre un changement d'etablissement : le
     * rattachement doit alors etre recalcule, sans quoi le candidat
     * resterait compte du cote de son ancien etablissement.
     */
    public function etablissementDoitEtreResolu(): bool
    {
        return $this->etablissement_id === null || $this->isDirty('classe_id');
    }

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

    /**
     * Vérifier si le candidat a une photo
     */
    public function hasPhoto(): bool
    {
        return !empty($this->photo);
    }

    /**
     * Obtenir l'URL de la photo
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        return asset("storage/photos_cl/{$this->classe->segment_photos}/{$this->photo}");
    }

    /**
     * Obtenir le code du candidat (toujours CL pour candidat libre)
     */
    public function getCodeAttribute(): string
    {
        return 'CL';
    }

    /**
     * Obtenir le code école (toujours 0000 pour candidat libre)
     */
    public function getSchoolCodeAttribute(): string
    {
        return '0000';
    }
}