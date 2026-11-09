<?php
// =============================================================================

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Services\CardGenerationService;
use App\Services\PhotoProcessingService;
use App\Services\Photo4x4Service;
use Illuminate\Http\Request;
use ZipArchive;

class CardController extends Controller
{
    private $cardService;
    private $photoService;
    private $photo4x4Service;
    
    public function __construct(
        CardGenerationService $cardService,
        PhotoProcessingService $photoService,
        Photo4x4Service $photo4x4Service
    ) {
        $this->cardService = $cardService;
        $this->photoService = $photoService;
        $this->photo4x4Service = $photo4x4Service;
    }
    
    public function index()
    {
        $classes = Classe::withCount('etudiants')->get();
        
        // Calculer les statistiques de photos pour chaque classe
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
        
        $totalWithPhotos = $classes->sum('students_with_photos');
        
        return view('cards.index', compact('classes', 'totalWithPhotos'));
    }
    
    public function generateCards(Classe $classe)
    {
        try {
            $filePath = $this->cardService->generateCards($classe);
            
            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération des cartes : ' . $e->getMessage());
        }
    }
    
    public function generateProcessedPhotos(Classe $classe)
    {
        try {
            $zipPath = $this->photoService->generateProcessedPhotos($classe);
            
            return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du traitement des photos : ' . $e->getMessage());
        }
    }
    
    public function generatePrintablePhotos(Classe $classe)
    {
        try {
            $filePath = $this->photo4x4Service->generatePrintablePhotos($classe);
            
            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }
    
    public function generateAll(Classe $classe)
    {
        try {
            $cardPath = $this->cardService->generateCards($classe);
            $photosZipPath = $this->photoService->generateProcessedPhotos($classe);
            $printablePath = $this->photo4x4Service->generatePrintablePhotos($classe);
            
            // Créer un ZIP contenant tous les fichiers
            $finalZipName = "Complet_{$classe->nom}_" . date('Ymd_His') . ".zip";
            $finalZipPath = storage_path("app/public/generated/{$finalZipName}");
            
            if (!file_exists(dirname($finalZipPath))) {
                mkdir(dirname($finalZipPath), 0777, true);
            }
            
            $zip = new ZipArchive();
            $result = $zip->open($finalZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== true) {
                throw new \Exception("Impossible de créer le ZIP final");
            }
            
            $zip->addFile($cardPath, 'Cartes_Scolaires/' . basename($cardPath));
            $zip->addFile($photosZipPath, 'Photos_Traitees/' . basename($photosZipPath));
            $zip->addFile($printablePath, 'Photos_Imprimables/' . basename($printablePath));
            
            $zip->close();
            
            // Nettoyer les fichiers temporaires
            unlink($cardPath);
            unlink($photosZipPath);
            unlink($printablePath);
            
            return response()->download($finalZipPath, $finalZipName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération complète : ' . $e->getMessage());
        }
    }
    
    public function generateAllGlobal()
    {
        try {
            $classes = Classe::withCount('etudiants')
                ->whereHas('etudiants', function($query) {
                    $query->whereNotNull('photo')->where('photo', '!=', '');
                })
                ->get();
            
            if ($classes->isEmpty()) {
                return back()->with('error', 'Aucune classe avec photos disponible.');
            }
            
            // Générer un PDF global avec toutes les cartes
            $globalPdfName = "Cartes_Globales_" . date('Ymd_His') . ".pdf";
            $globalPdfPath = storage_path("app/public/generated/{$globalPdfName}");
            
            if (!file_exists(dirname($globalPdfPath))) {
                mkdir(dirname($globalPdfPath), 0777, true);
            }
            
            // Utiliser le service pour générer toutes les cartes dans un seul PDF
            $this->cardService->generateGlobalCards($classes, $globalPdfPath);
            
            return response()->download($globalPdfPath, $globalPdfName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération globale : ' . $e->getMessage());
        }
    }
}