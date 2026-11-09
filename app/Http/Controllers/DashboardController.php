<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $totalClasses = Classe::count();
        $totalStudents = Etudiant::count();
        $totalBoys = Etudiant::where('sexe', 'M')->count();
        $totalGirls = Etudiant::where('sexe', 'F')->count();
        
        // Étudiants avec photos
        $totalWithPhotos = Etudiant::whereNotNull('photo')
            ->where('photo', '!=', '')
            ->count();
        
        // Classes avec leurs statistiques détaillées
        $classes = Classe::withCount('etudiants')
            ->with(['etudiants' => function($query) {
                $query->select('classe_id', 'sexe');
            }])
            ->orderBy('nom')
            ->get()
            ->map(function($classe) {
                // Calculer les effectifs par sexe
                $classe->effectif_garcons = $classe->etudiants->where('sexe', 'M')->count();
                $classe->effectif_filles = $classe->etudiants->where('sexe', 'F')->count();
                
                return $classe;
            });
        
        // Statistiques supplémentaires (optionnelles)
        $stats = [
            'total_classes' => $totalClasses,
            'total_students' => $totalStudents,
            'total_boys' => $totalBoys,
            'total_girls' => $totalGirls,
            'total_with_photos' => $totalWithPhotos,
            'percentage_with_photos' => $totalStudents > 0 
                ? round(($totalWithPhotos / $totalStudents) * 100, 1) 
                : 0,
            'percentage_boys' => $totalStudents > 0 
                ? round(($totalBoys / $totalStudents) * 100, 1) 
                : 0,
            'percentage_girls' => $totalStudents > 0 
                ? round(($totalGirls / $totalStudents) * 100, 1) 
                : 0,
        ];
        
        // Statistiques par classe (pour graphiques avancés)
        $classeStats = Classe::select('classes.*')
            ->withCount('etudiants')
            ->with(['etudiants' => function($query) {
                $query->select('classe_id', 'sexe', 'photo');
            }])
            ->get()
            ->map(function($classe) {
                return [
                    'nom' => $classe->nom,
                    'total' => $classe->etudiants_count,
                    'garcons' => $classe->etudiants->where('sexe', 'M')->count(),
                    'filles' => $classe->etudiants->where('sexe', 'F')->count(),
                    'avec_photo' => $classe->etudiants->whereNotNull('photo')
                        ->where('photo', '!=', '')
                        ->count(),
                ];
            });
        
        // Statistiques par année scolaire
        $anneesUniques = Classe::distinct()->pluck('annee_scolaire');
        
        $statsParAnnee = $anneesUniques->map(function($annee) {
            return [
                'annee' => $annee,
                'total_classes' => Classe::where('annee_scolaire', $annee)->count(),
                'total_etudiants' => Etudiant::whereHas('classe', function($query) use ($annee) {
                    $query->where('annee_scolaire', $annee);
                })->count(),
            ];
        });
        
        // Dernières activités (optionnel - si vous avez un système de logs)
        $recentActivities = [
            // Dernières classes créées
            'recent_classes' => Classe::latest()->take(5)->get(),
            
            // Derniers étudiants ajoutés
            'recent_students' => Etudiant::with('classe')
                ->latest()
                ->take(5)
                ->get(),
        ];
        
        return view('dashboard', compact(
            'totalClasses',
            'totalStudents', 
            'totalBoys',
            'totalGirls',
            'totalWithPhotos',
            'classes',
            'stats',
            'classeStats',
            'statsParAnnee',
            'recentActivities'
        ));
    }
    
    /**
     * Obtenir les statistiques au format JSON (pour AJAX/API)
     */
    public function getStats()
    {
        $stats = [
            'general' => [
                'total_classes' => Classe::count(),
                'total_students' => Etudiant::count(),
                'total_boys' => Etudiant::where('sexe', 'M')->count(),
                'total_girls' => Etudiant::where('sexe', 'F')->count(),
                'total_with_photos' => Etudiant::whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->count(),
            ],
            
            'by_class' => Classe::withCount('etudiants')
                ->with(['etudiants' => function($query) {
                    $query->select('classe_id', 'sexe', 'photo');
                }])
                ->get()
                ->map(function($classe) {
                    return [
                        'id' => $classe->id,
                        'nom' => $classe->nom,
                        'annee_scolaire' => $classe->annee_scolaire,
                        'exam' => $classe->exam,
                        'total' => $classe->etudiants_count,
                        'garcons' => $classe->etudiants->where('sexe', 'M')->count(),
                        'filles' => $classe->etudiants->where('sexe', 'F')->count(),
                        'avec_photo' => $classe->etudiants->whereNotNull('photo')
                            ->where('photo', '!=', '')
                            ->count(),
                    ];
                }),
            
            'by_gender' => [
                'boys' => Etudiant::where('sexe', 'M')->count(),
                'girls' => Etudiant::where('sexe', 'F')->count(),
            ],
            
            'by_status' => [
                'normal' => Etudiant::where('statut', 'N')->count(),
                'redoublant' => Etudiant::where('statut', 'R')->count(),
            ],
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Obtenir les statistiques d'une classe spécifique
     */
    public function getClasseStats(Classe $classe)
    {
        $stats = [
            'classe' => [
                'id' => $classe->id,
                'nom' => $classe->nom,
                'annee_scolaire' => $classe->annee_scolaire,
                'exam' => $classe->exam,
            ],
            'effectifs' => [
                'total' => $classe->etudiants()->count(),
                'garcons' => $classe->etudiants()->where('sexe', 'M')->count(),
                'filles' => $classe->etudiants()->where('sexe', 'F')->count(),
            ],
            'photos' => [
                'avec_photo' => $classe->etudiants()
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->count(),
                'sans_photo' => $classe->etudiants()
                    ->where(function($query) {
                        $query->whereNull('photo')
                            ->orWhere('photo', '');
                    })
                    ->count(),
            ],
            'statuts' => [
                'normal' => $classe->etudiants()->where('statut', 'N')->count(),
                'redoublant' => $classe->etudiants()->where('statut', 'R')->count(),
            ],
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Obtenir un résumé rapide pour le header
     */
    public function quickStats()
    {
        return response()->json([
            'classes' => Classe::count(),
            'students' => Etudiant::count(),
            'photos' => Etudiant::whereNotNull('photo')
                ->where('photo', '!=', '')
                ->count(),
        ]);
    }
}