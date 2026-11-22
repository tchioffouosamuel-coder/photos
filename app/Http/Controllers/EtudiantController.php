<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EtudiantController extends Controller
{
    public function create(Classe $classe)
    {
        return view('etudiants.create', compact('classe'));
    }
    
    public function store(Request $request, Classe $classe)
    {
        $validated = $request->validate([
            'matricule_national' => 'nullable|string|max:255',
            'matricule_interne' => 'nullable|string|max:255',
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'statut' => 'required|in:N,R',
            'photo' => 'nullable|image|max:5120' // 5MB max
        ]);
        
        $validated['classe_id'] = $classe->id;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("photos/{$classe->segment_photos}", 'public');
            $validated['photo'] = basename($photoPath);
        }
        
        Etudiant::create($validated);
        
        return redirect()->route('classes.show', $classe)
            ->with('success', 'Élève ajouté avec succès');
    }
    
    public function edit(Classe $classe, Etudiant $etudiant)
    {
        // Vérifier que l'étudiant appartient à cette classe
        if ($etudiant->classe_id !== $classe->id) {
            abort(404);
        }

        // Classes disponibles comme destination de transfert
        $autresClasses = Classe::where('id', '!=', $classe->id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'annee_scolaire']);

        return view('etudiants.edit', compact('classe', 'etudiant', 'autresClasses'));
    }
    
    public function update(Request $request, Classe $classe, Etudiant $etudiant)
    {
        // Vérifier que l'étudiant appartient à cette classe
        if ($etudiant->classe_id !== $classe->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'matricule_national' => 'nullable|string|max:255',
            'matricule_interne' => 'nullable|string|max:255',
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'statut' => 'required|in:N,R',
            'photo' => 'nullable|image|max:5120'
        ]);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($etudiant->photo) {
                Storage::disk('public')->delete("photos/{$classe->segment_photos}/{$etudiant->photo}");
            }
            
            $photoPath = $request->file('photo')->store("photos/{$classe->segment_photos}", 'public');
            $validated['photo'] = basename($photoPath);
        }
        
        $etudiant->update($validated);
        
        return redirect()->route('classes.show', $classe)
            ->with('success', 'Élève mis à jour avec succès');
    }
    
    public function destroy(Classe $classe, Etudiant $etudiant)
    {
        // Vérifier que l'étudiant appartient à cette classe
        if ($etudiant->classe_id !== $classe->id) {
            abort(404);
        }
        
        // Delete photo if exists
        if ($etudiant->photo) {
            Storage::disk('public')->delete("photos/{$classe->segment_photos}/{$etudiant->photo}");
        }
        
        $etudiant->delete();
        
        return redirect()->route('classes.show', $classe)
            ->with('success', 'Élève supprimé avec succès');
    }
}