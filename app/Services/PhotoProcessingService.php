<?php
namespace App\Services;

use App\Models\Classe;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class PhotoProcessingService
{
    private $photoWidth = 800;
    private $photoHeight = 1000;
    private ImageManager $manager;
    
    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    
    public function generateProcessedPhotos(Classe $classe): string
    {
        $etudiants = $classe->etudiants()
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->orderBy('nom')
            ->get();
        
        if ($etudiants->isEmpty()) {
            throw new \Exception("Aucun étudiant avec photo trouvé pour cette classe.");
        }
        
        $tempDir = storage_path("app/temp/photos_{$classe->nom}_" . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $date = date("d/m/Y");
        $schoolCode = config('school.code', '1357');
        
        $successCount = 0;
        foreach ($etudiants as $etudiant) {
            try {
                $this->processPhoto($etudiant, $tempDir, $date, $schoolCode);
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Erreur traitement photo {$etudiant->nom}: " . $e->getMessage());
            }
        }
        
        if ($successCount === 0) {
            throw new \Exception("Aucune photo n'a pu être traitée avec succès.");
        }
        
        $zipFileName = "Photos_Traitees_{$classe->nom}_" . date('Ymd_His') . ".zip";
        $zipPath = storage_path("app/public/generated/{$zipFileName}");
        
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }
        
        try {
            $this->createZip($tempDir, $zipPath);
        } catch (\Exception $e) {
            $this->deleteDirectory($tempDir);
            throw $e;
        }
        
        $this->deleteDirectory($tempDir);
        
        return $zipPath;
    }
    
    private function processPhoto($etudiant, $tempDir, $date, $schoolCode)
    {
        $photoPath = storage_path("app/public/photos/{$etudiant->classe->nom}/{$etudiant->photo}");
        
        if (!file_exists($photoPath)) {
            throw new \Exception("Photo introuvable: {$photoPath}");
        }
        
        // Créer un canvas blanc
        $img = $this->manager->create($this->photoWidth, $this->photoHeight)
            ->fill('ffffff');
        
        // Définir les zones de texte
        $topTextHeight = 100;  // Zone pour la date en haut
        $bottomTextHeight = 100; // Zone pour nom et infos en bas
        
        // Zone disponible pour la photo
        $availableHeight = $this->photoHeight - $topTextHeight - $bottomTextHeight;
        $availableWidth = $this->photoWidth;
        
        try {
            $studentPhoto = $this->manager->read($photoPath);
            
            // Calculer le ratio pour remplir toute la zone disponible
            $widthRatio = $availableWidth / $studentPhoto->width();
            $heightRatio = $availableHeight / $studentPhoto->height();
            
            // Utiliser le plus grand ratio pour couvrir toute la zone
            $ratio = max($widthRatio, $heightRatio);
            
            $newWidth = (int)($studentPhoto->width() * $ratio);
            $newHeight = (int)($studentPhoto->height() * $ratio);
            
            // Redimensionner la photo
            $studentPhoto->scale($newWidth, $newHeight);
            
            // Centrer la photo dans la zone disponible
            $photoX = (int)(($this->photoWidth - $newWidth) / 2);
            $photoY = $topTextHeight + (int)(($availableHeight - $newHeight) / 2);
            
            // Placer la photo
            $img->place($studentPhoto, 'top-left', $photoX, $photoY);
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors du chargement de l'image: " . $e->getMessage());
        }
        
        // Ajouter la date en haut
        $img->text($date, (int)($this->photoWidth / 2), 60, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(40);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Ajouter le nom en bas
        $nomComplet = strtoupper($etudiant->nom);
        $img->text($nomComplet, (int)($this->photoWidth / 2), 940, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(35);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Ajouter l'examen et le code
        $examInfo = strtoupper($etudiant->classe->exam ?? 'EXAMEN') . ' ' . 
                    $this->getStudentCode($etudiant) . ' ' . 
                    $schoolCode;
        
        $img->text($examInfo, (int)($this->photoWidth / 2), 985, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(32);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Sauvegarder
        $fileName = $this->sanitizeFileName($etudiant->nom ?: $etudiant->matricule_national) . '.jpg';
        $img->toJpeg(90)->save($tempDir . '/' . $fileName);
    }
    
    private function getStudentCode($etudiant)
    {
        if (strpos($etudiant->lieu_naissance, ',') !== false) {
            $parts = explode(',', $etudiant->lieu_naissance);
            return trim($parts[1]);
        }
        return 'CR';
    }
    
    private function sanitizeFileName($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        return $name;
    }
    
    private function createZip($sourceDir, $zipPath)
    {
        $zip = new ZipArchive();
        
        $zipPath = str_replace('/', DIRECTORY_SEPARATOR, $zipPath);
        $sourceDir = str_replace('/', DIRECTORY_SEPARATOR, $sourceDir);
        
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result !== true) {
            throw new \Exception("Impossible de créer le fichier ZIP. Code erreur: {$result}");
        }
        
        if (!is_dir($sourceDir)) {
            throw new \Exception("Le dossier source n'existe pas: {$sourceDir}");
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        $fileCount = 0;
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                
                if ($zip->addFile($filePath, $relativePath)) {
                    $fileCount++;
                } else {
                    \Log::warning("Impossible d'ajouter le fichier au ZIP: {$filePath}");
                }
            }
        }
        
        if ($fileCount === 0) {
            throw new \Exception("Aucun fichier n'a été ajouté au ZIP");
        }
        
        $zip->close();
        
        if (!file_exists($zipPath)) {
            throw new \Exception("Le fichier ZIP n'a pas été créé: {$zipPath}");
        }
        
        \Log::info("ZIP créé avec succès: {$zipPath} ({$fileCount} fichiers)");
    }
    
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}