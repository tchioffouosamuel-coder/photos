<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
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
    
    public function getFullNameAttribute()
    {
        return "{$this->nom} {$this->prenoms}";
    }
    
    public function getPhotoPathAttribute()
    {
        if (!$this->photo) return null;
        return storage_path("app/public/photos/{$this->classe->nom}/{$this->photo}");
    }
}