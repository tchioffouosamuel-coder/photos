<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use App\Models\Concerns\PorteeParClasse;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use AppartientAEtablissement, PorteeParClasse;

    protected $table = 'etudiants';
    protected $fillable = [
        'matricule_national', 'matricule_interne', 'nom', 'prenoms', 
        'date_naissance', 'lieu_naissance', 'sexe', 'photo', 
        'contact_parent', 'statut', 'classe_id'
    ];
    
    protected $casts = [
        'date_naissance' => 'date'
    ];
    
    public function classe()
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
     * rattachement doit alors etre recalcule, sans quoi l'eleve resterait
     * compte du cote de son ancien etablissement.
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
    
    public function getFullNameAttribute()
    {
        return "{$this->nom} {$this->prenoms}";
    }
    
    public function getPhotoPathAttribute()
    {
        if (!$this->photo) return null;
        return storage_path("app/public/photos/{$this->classe->segment_photos}/{$this->photo}");
    }
}