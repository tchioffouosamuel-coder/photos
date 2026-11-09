<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    protected $fillable = [
        'nom',
        'exam',
        'annee_scolaire',
        'effectif_total', 'effectif_garcons', 'effectif_filles'
    ];

    /**
     * Relation avec les étudiants réguliers
     */
    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class);
    }

    /**
     * Relation avec les candidats libres
     */
    public function candidatsLibres(): HasMany
    {
        return $this->hasMany(CandidatLibre::class);
    }

    /**
     * Calculer l'effectif des garçons (étudiants réguliers)
     */
    public function getEffectifGarconsAttribute(): int
    {
        return $this->etudiants()->where('sexe', 'M')->count();
    }

    /**
     * Calculer l'effectif des filles (étudiants réguliers)
     */
    public function getEffectifFillesAttribute(): int
    {
        return $this->etudiants()->where('sexe', 'F')->count();
    }

    /**
     * Vérifier si c'est une classe d'examen
     */
    public function isExamClass(): bool
    {
        return !empty($this->exam);
    }

    /**
     * Obtenir le total des candidats (réguliers + libres)
     */
    public function getTotalCandidatsAttribute(): int
    {
        return $this->etudiants()->count() + $this->candidatsLibres()->count();
    }

    /**
     * Obtenir le nombre de candidats libres garçons
     */
    public function getCandidatsLibresGarconsAttribute(): int
    {
        return $this->candidatsLibres()->where('sexe', 'M')->count();
    }
 public function updateEffectifs()
    {
        $this->effectif_total = $this->etudiants()->count();
        $this->effectif_garcons = $this->etudiants()->where('sexe', 'M')->count();
        $this->effectif_filles = $this->etudiants()->where('sexe', 'F')->count();
        $this->save();
    }
    /**
     * Obtenir le nombre de candidats libres filles
     */
    public function getCandidatsLibresFillesAttribute(): int
    {
        return $this->candidatsLibres()->where('sexe', 'F')->count();
    }
}