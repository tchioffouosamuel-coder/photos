<?php

namespace App\Services;

use App\Models\Classe;
use Codedge\Fpdf\Fpdf\Fpdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Photo4x4Service
{
    private $blockSize = 40; // Taille en mm
    private $margin = 5;     // Marge en mm
    private ImageManager $manager;
    
    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    
    public function generatePrintablePhotos(Classe $classe): string
    {
        $etudiants = $classe->etudiants()
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->orderBy('nom')
            ->get();
        
        if ($etudiants->isEmpty()) {
            throw new \Exception("Aucun étudiant avec photo trouvé pour cette classe.");
        }
        
        // Créer un dossier temporaire pour les photos traitées
        $tempDir = storage_path("app/temp/photos_4x4_{$classe->nom}_" . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $date = date("d/m/Y");
        $schoolCode = config('school.code', '1357');
        
        // Traiter chaque photo
        $processedPhotos = [];
        foreach ($etudiants as $etudiant) {
            try {
                $photoPath = $this->createProcessedPhoto($etudiant, $tempDir, $date, $schoolCode);
                $processedPhotos[] = [
                    'path' => $photoPath,
                    'etudiant' => $etudiant
                ];
            } catch (\Exception $e) {
                \Log::error("Erreur traitement photo {$etudiant->nom}: " . $e->getMessage());
            }
        }
        
        if (empty($processedPhotos)) {
            throw new \Exception("Aucune photo n'a pu être traitée.");
        }
        
        // Créer le PDF avec les photos traitées
        $pdfPath = $this->createPDF($classe, $processedPhotos);
        
        // Nettoyer le dossier temporaire
        $this->deleteDirectory($tempDir);
        
        return $pdfPath;
    }
    
    private function createProcessedPhoto($etudiant, $tempDir, $date, $schoolCode)
    {
        $photoPath = storage_path("app/public/photos/{$etudiant->classe->nom}/{$etudiant->photo}");
        
        if (!file_exists($photoPath)) {
            throw new \Exception("Photo introuvable: {$photoPath}");
        }
        
        // Taille de la photo finale (40mm = ~151px à 96dpi)
        $finalWidth = 151;
        $finalHeight = 189; // Ratio 4:5 pour format photo standard
        
        // Créer un canvas blanc
        $img = $this->manager->create($finalWidth, $finalHeight)
            ->fill('ffffff');
        
        // Zones de texte
        $topTextHeight = 30;
        $bottomTextHeight = 40;
        $availableHeight = $finalHeight - $topTextHeight - $bottomTextHeight;
        
        try {
            $studentPhoto = $this->manager->read($photoPath);
            
            // Calculer le ratio pour remplir la zone
            $widthRatio = $finalWidth / $studentPhoto->width();
            $heightRatio = $availableHeight / $studentPhoto->height();
            $ratio = max($widthRatio, $heightRatio);
            
            $newWidth = (int)($studentPhoto->width() * $ratio);
            $newHeight = (int)($studentPhoto->height() * $ratio);
            
            $studentPhoto->scale($newWidth, $newHeight);
            
            // Centrer la photo
            $photoX = (int)(($finalWidth - $newWidth) / 2);
            $photoY = $topTextHeight + (int)(($availableHeight - $newHeight) / 2);
            
            $img->place($studentPhoto, 'top-left', $photoX, $photoY);
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur chargement image: " . $e->getMessage());
        }
        
        // Ajouter la date en haut
        $img->text($date, (int)($finalWidth / 2), 15, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(12);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Ajouter le nom en bas
        $nomComplet = strtoupper($etudiant->nom);
        // Tronquer si trop long
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
        
        // Ajouter l'examen et le code
        $examInfo = strtoupper($etudiant->classe->exam ?? 'EXAM') . ' ' . 
                    $this->getStudentCode($etudiant) . ' ' . 
                    $schoolCode;
        
        $img->text($examInfo, (int)($finalWidth / 2), $finalHeight - 10, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(12);
            $font->color('0000ff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Sauvegarder
        $fileName = $this->sanitizeFileName($etudiant->nom ?: $etudiant->matricule_national) . '.jpg';
        $filePath = $tempDir . '/' . $fileName;
        $img->toJpeg(90)->save($filePath);
        
        return $filePath;
    }
    
    private function createPDF($classe, $processedPhotos)
    {
        $pdf = new class extends Fpdf {
            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
            }
        };
        
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        // Titre
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, mb_convert_encoding("Photos - {$classe->nom}", 'windows-1252', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Position initiale
        $x = 10;
        $y = $pdf->GetY();
        $imagesPerRow = 4;
        $imageCount = 0;
        
        foreach ($processedPhotos as $photoData) {
            try {
                // Dessiner un cadre
                $pdf->Rect($x, $y, $this->blockSize, $this->blockSize, 'D');
                
                // Ajouter l'image traitée
                $pdf->Image($photoData['path'], $x, $y, $this->blockSize, $this->blockSize);
                
                // Position suivante
                $imageCount++;
                $x += $this->blockSize + $this->margin;
                
                // Nouvelle ligne après 4 images
                if ($imageCount % $imagesPerRow == 0) {
                    $x = 10;
                    $y += $this->blockSize + $this->margin;
                    
                    // Nouvelle page si nécessaire
                    if ($y + $this->blockSize > 280) {
                        $pdf->AddPage();
                        $y = 20;
                    }
                }
                
            } catch (\Exception $e) {
                \Log::error("Erreur ajout image au PDF: " . $e->getMessage());
            }
        }
        
        // Sauvegarder le PDF
        $fileName = "Photos_4x4_{$classe->nom}_" . date('Ymd_His') . ".pdf";
        $filePath = storage_path("app/public/generated/{$fileName}");
        
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        
        $pdf->Output('F', $filePath);
        
        return $filePath;
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