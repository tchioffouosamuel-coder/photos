<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatLibre extends Model
{
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

        return asset("storage/photos_cl/{$this->classe->nom}/{$this->photo}");
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