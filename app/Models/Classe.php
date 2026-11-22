<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use App\Models\Concerns\PorteeParClasse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    use AppartientAEtablissement, PorteeParClasse;

    /** Sur une classe, la colonne de portee est sa propre cle. */
    public static function colonneClasse(): string
    {
        return 'id';
    }

    /*
     | etablissement_id n'est volontairement pas remplissable en masse : il
     | est derive du perimetre de la requete (voir AppartientAEtablissement),
     | jamais recopie depuis un formulaire.
     */
    protected $fillable = [
        'nom',
        'exam',
        'annee_scolaire',
        'effectif_total', 'effectif_garcons', 'effectif_filles'
    ];

    /**
     * Segment de dossier ou sont rangees les photos de cette classe.
     *
     * Le nom de la classe seul ne suffit plus : deux etablissements ayant
     * tous deux une « Tle A4 » melangeraient leurs fichiers. Le segment de
     * l'etablissement est donc place devant.
     *
     * L'etablissement d'origine a un prefixe NULL et garde donc ses chemins
     * historiques : la mise en multi-etablissement ne deplace aucun fichier
     * deja present sur le serveur.
     */
    public function getSegmentPhotosAttribute(): string
    {
        $prefixe = $this->etablissement?->segmentPhotos() ?? '';

        return $prefixe === '' ? (string) $this->nom : $prefixe . '/' . $this->nom;
    }

    /**
     * Code de l'etablissement, imprime sur les documents de cette classe.
     */
    public function codeEtablissement(): string
    {
        return $this->etablissement?->codeDocument()
            ?: (string) config('school.code', '');
    }

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