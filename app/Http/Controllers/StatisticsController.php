<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index()
    {
        $classes = Classe::withCount('etudiants')->get();
        
        // Calculer les statistiques pour chaque classe
        $classes->transform(function ($classe) {
            $classe->students_with_photos = $classe->etudiants()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->count();
            
            $classe->students_without_photos = $classe->etudiants_count - $classe->students_with_photos;
            
            $classe->photo_percentage = $classe->etudiants_count > 0 
                ? ($classe->students_with_photos / $classe->etudiants_count) * 100 
                : 0;
            
            return $classe;
        });
        
        // Statistiques globales
        $totalClasses = $classes->count();
        $totalStudents = $classes->sum('etudiants_count');
        $studentsWithPhotos = $classes->sum('students_with_photos');
        $studentsWithoutPhotos = $classes->sum('students_without_photos');
        $photoPercentage = $totalStudents > 0 
            ? ($studentsWithPhotos / $totalStudents) * 100 
            : 0;
        
        // Statistiques par sexe
        $totalBoys = Etudiant::where('sexe', 'M')->count();
        $totalGirls = Etudiant::where('sexe', 'F')->count();
        
        // Statistiques par statut
        $normalStudents = Etudiant::where('statut', 'N')->count();
        $redoublants = Etudiant::where('statut', 'R')->count();
        
        return view('statistics.index', compact(
            'classes',
            'totalClasses',
            'totalStudents',
            'studentsWithPhotos',
            'studentsWithoutPhotos',
            'photoPercentage',
            'totalBoys',
            'totalGirls',
            'normalStudents',
            'redoublants'
        ));
    }
    
    public function noPhotos()
    {
        $students = Etudiant::with('classe')
            ->whereNull('photo')
            ->orWhere('photo', '')
            ->orderBy('nom')
            ->get();
        
        $classes = Classe::has('etudiants')->orderBy('nom')->get();
        
        $totalWithoutPhotos = $students->count();
        $classesAffected = $students->pluck('classe_id')->unique()->count();
        $boysWithoutPhotos = $students->where('sexe', 'M')->count();
        $girlsWithoutPhotos = $students->where('sexe', 'F')->count();
        
        return view('statistics.no-photos', compact(
            'students',
            'classes',
            'totalWithoutPhotos',
            'classesAffected',
            'boysWithoutPhotos',
            'girlsWithoutPhotos'
        ));
    }
}

