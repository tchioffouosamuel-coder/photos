<?php
namespace App\Services;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\CandidatLibre;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;

class UnifiedPhotoService
{
    private $photoWidth = 800;
    private $photoHeight = 1000;
    private ImageManager $manager;
    
    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    
    /**
     * Générer les photos traitées (réguliers ou candidats libres)
     */
    public function generateProcessedPhotos(Classe $classe, bool $candidatsLibresOnly = false): string
    {
        if ($candidatsLibresOnly) {
            $subjects = $classe->candidatsLibres()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->orderBy('nom')
                ->get();
            
            $type = 'candidats_libres';
            $photoDir = 'photos_cl';
        } else {
            $subjects = $classe->etudiants()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->orderBy('nom')
                ->get();
            
            $type = 'etudiants';
            $photoDir = 'photos';
        }
        
        if ($subjects->isEmpty()) {
            throw new \Exception("Aucune photo trouvée pour cette classe.");
        }
        
        $tempDir = storage_path("app/temp/{$type}_{$classe->nom}_" . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $date = date("d/m/Y");
        $successCount = 0;
        
        foreach ($subjects as $subject) {
            try {
                $this->processPhoto($subject, $tempDir, $date, $photoDir, $candidatsLibresOnly);
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Erreur traitement photo {$subject->nom}: " . $e->getMessage());
            }
        }
        
        if ($successCount === 0) {
            throw new \Exception("Aucune photo n'a pu être traitée.");
        }
        
        $zipFileName = "Photos_" . ($candidatsLibresOnly ? 'CL_' : '') . "{$classe->nom}_" . date('Ymd_His') . ".zip";
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
    
    /**
     * Traiter une photo individuelle
     */
    private function processPhoto($subject, $tempDir, $date, $photoDir, $isCandidatLibre)
    {
        $photoPath = storage_path("app/public/{$photoDir}/{$subject->classe->nom}/{$subject->photo}");
        
        if (!file_exists($photoPath)) {
            throw new \Exception("Photo introuvable: {$photoPath}");
        }
        
        // Créer canvas
        $img = $this->manager->create($this->photoWidth, $this->photoHeight)
            ->fill('ffffff');
        
        $topTextHeight = 100;
        $bottomTextHeight = 100;
        $availableHeight = $this->photoHeight - $topTextHeight - $bottomTextHeight;
        $availableWidth = $this->photoWidth;
        
        try {
            $studentPhoto = $this->manager->read($photoPath);
            
            $widthRatio = $availableWidth / $studentPhoto->width();
            $heightRatio = $availableHeight / $studentPhoto->height();
            $ratio = max($widthRatio, $heightRatio);
            
            $newWidth = (int)($studentPhoto->width() * $ratio);
            $newHeight = (int)($studentPhoto->height() * $ratio);
            
            $studentPhoto->scale($newWidth, $newHeight);
            
            $photoX = (int)(($this->photoWidth - $newWidth) / 2);
            $photoY = $topTextHeight + (int)(($availableHeight - $newHeight) / 2);
            
            $img->place($studentPhoto, 'top-left', $photoX, $photoY);
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur chargement image: " . $e->getMessage());
        }
        
        // Date
        $img->text($date, (int)($this->photoWidth / 2), 60, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(40);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Nom
        $nomComplet = strtoupper($subject->nom);
        $img->text($nomComplet, (int)($this->photoWidth / 2), 940, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(35);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Info examen
        if ($isCandidatLibre) {
            $schoolCode = '0000';
            $code = 'CL';
        } else {
            $schoolCode = config('school.code', '1357');
            $code = $this->getStudentCode($subject);
        }
        
        $examInfo = strtoupper($subject->classe->exam ?? 'EXAMEN') . ' ' . $code . ' ' . $schoolCode;
        
        $img->text($examInfo, (int)($this->photoWidth / 2), 985, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(32);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Sauvegarder
        $fileName = $this->sanitizeFileName($subject->nom) . '.jpg';
        $img->toJpeg(90)->save($tempDir . '/' . $fileName);
    }
    
    /**
     * Générer les photos 4x4 pour impression
     */
    public function generate4x4Photos(Classe $classe, bool $candidatsLibresOnly = false): string
    {
        if ($candidatsLibresOnly) {
            $subjects = $classe->candidatsLibres()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->orderBy('nom')
                ->get();
            
            $photoDir = 'photos_cl';
        } else {
            $subjects = $classe->etudiants()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->orderBy('nom')
                ->get();
            
            $photoDir = 'photos';
        }
        
        if ($subjects->isEmpty()) {
            throw new \Exception("Aucune photo trouvée.");
        }
        
        $tempDir = storage_path("app/temp/4x4_{$classe->nom}_" . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $date = date("d/m/Y");
        $processedPhotos = [];
        
        foreach ($subjects as $subject) {
            try {
                $photoPath = $this->create4x4Photo($subject, $tempDir, $date, $photoDir, $candidatsLibresOnly);
                $processedPhotos[] = [
                    'path' => $photoPath,
                    'subject' => $subject
                ];
            } catch (\Exception $e) {
                \Log::error("Erreur 4x4 {$subject->nom}: " . $e->getMessage());
            }
        }
        
        if (empty($processedPhotos)) {
            throw new \Exception("Aucune photo 4x4 créée.");
        }
        
        $pdfPath = $this->create4x4PDF($classe, $processedPhotos, $candidatsLibresOnly);
        $this->deleteDirectory($tempDir);
        
        return $pdfPath;
    }
    
    /**
     * Créer une photo 4x4
     */
    private function create4x4Photo($subject, $tempDir, $date, $photoDir, $isCandidatLibre)
    {
        $photoPath = storage_path("app/public/{$photoDir}/{$subject->classe->nom}/{$subject->photo}");
        
        if (!file_exists($photoPath)) {
            throw new \Exception("Photo introuvable");
        }
        
        $finalWidth = 151;
        $finalHeight = 189;
        
        $img = $this->manager->create($finalWidth, $finalHeight)->fill('ffffff');
        
        $topTextHeight = 30;
        $bottomTextHeight = 40;
        $availableHeight = $finalHeight - $topTextHeight - $bottomTextHeight;
        
        try {
            $studentPhoto = $this->manager->read($photoPath);
            
            $widthRatio = $finalWidth / $studentPhoto->width();
            $heightRatio = $availableHeight / $studentPhoto->height();
            $ratio = max($widthRatio, $heightRatio);
            
            $newWidth = (int)($studentPhoto->width() * $ratio);
            $newHeight = (int)($studentPhoto->height() * $ratio);
            
            $studentPhoto->scale($newWidth, $newHeight);
            
            $photoX = (int)(($finalWidth - $newWidth) / 2);
            $photoY = $topTextHeight + (int)(($availableHeight - $newHeight) / 2);
            
            $img->place($studentPhoto, 'top-left', $photoX, $photoY);
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur image: " . $e->getMessage());
        }
        
        // Date
        $img->text($date, (int)($finalWidth / 2), 15, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(12);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Nom
        $nomComplet = strtoupper($subject->nom);
        if (strlen($nomComplet) > 30) {
            $nomComplet = substr($nomComplet, 0, 20);
        }
        
        $img->text($nomComplet, (int)($finalWidth / 2), $finalHeight - 25, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(11);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Info
        if ($isCandidatLibre) {
            $schoolCode = '0000';
            $code = 'CL';
        } else {
            $schoolCode = config('school.code', '1357');
            $code = $this->getStudentCode($subject);
        }
        
        $examInfo = strtoupper($subject->classe->exam ?? 'EXAM') . ' ' . $code . ' ' . $schoolCode;
        
        $img->text($examInfo, (int)($finalWidth / 2), $finalHeight - 10, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(12);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        $fileName = $this->sanitizeFileName($subject->nom) . '.jpg';
        $filePath = $tempDir . '/' . $fileName;
        $img->toJpeg(90)->save($filePath);
        
        return $filePath;
    }
    
    /**
     * Créer le PDF 4x4
     */
    private function create4x4PDF($classe, $processedPhotos, $isCandidatLibre)
    {
        $pdf = new \Codedge\Fpdf\Fpdf\Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        $title = "Photos 4x4 " . ($isCandidatLibre ? 'CL ' : '') . "- {$classe->nom}";
        $pdf->Cell(0, 10, mb_convert_encoding($title, 'windows-1252', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);
        
        $blockSize = 40;
        $margin = 5;
        $x = 10;
        $y = $pdf->GetY();
        $imagesPerRow = 4;
        $imageCount = 0;
        
        foreach ($processedPhotos as $photoData) {
            try {
                $pdf->Rect($x, $y, $blockSize, $blockSize, 'D');
                $pdf->Image($photoData['path'], $x, $y, $blockSize, $blockSize);
                
                $imageCount++;
                $x += $blockSize + $margin;
                
                if ($imageCount % $imagesPerRow == 0) {
                    $x = 10;
                    $y += $blockSize + $margin;
                    
                    if ($y + $blockSize > 280) {
                        $pdf->AddPage();
                        $y = 20;
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Erreur PDF: " . $e->getMessage());
            }
        }
        
        $fileName = "Photos_4x4_" . ($isCandidatLibre ? 'CL_' : '') . "{$classe->nom}_" . date('Ymd_His') . ".pdf";
        $filePath = storage_path("app/public/generated/{$fileName}");
        
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        
        $pdf->Output('F', $filePath);
        
        return $filePath;
    }
    
    /**
     * Helpers
     */
    private function getStudentCode($subject)
    {
        if (isset($subject->lieu_naissance) && strpos($subject->lieu_naissance, ',') !== false) {
            $parts = explode(',', $subject->lieu_naissance);
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
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Impossible de créer le ZIP");
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
                }
            }
        }
        
        $zip->close();
        
        if ($fileCount === 0) {
            throw new \Exception("Aucun fichier ajouté au ZIP");
        }
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