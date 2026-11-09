<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function index()
    {
        $classes = Classe::withCount('etudiants')->orderBy('nom')->get();
        return view('classes.index', compact('classes'));
    }
    
    public function create()
    {
        return view('classes.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:classes',
            'exam' => 'nullable|string|max:255',
            'annee_scolaire' => 'required|string|max:20'
        ]);
        
        Classe::create($validated);
        
        return redirect()->route('classes.index')->with('success', 'Classe créée avec succès');
    }
    
    public function show(Classe $classe)
    {
        $classe->load(['etudiants' => function($query) {
            $query->orderBy('nom');
        }]);
        
        return view('classes.show', compact('classe'));
    }
    
    public function edit(Classe $classe)
    {
        return view('classes.edit', compact('classe'));
    }
    
    public function update(Request $request, Classe $classe)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:classes,nom,' . $classe->id,
            'exam' => 'nullable|string|max:255',
            'annee_scolaire' => 'required|string|max:20'
        ]);
        
        $classe->update($validated);
        
        return redirect()->route('classes.show', $classe)->with('success', 'Classe mise à jour');
    }
    
    public function destroy(Classe $classe)
    {
        $classe->delete();
        return redirect()->route('classes.index')->with('success', 'Classe supprimée');
    }
}