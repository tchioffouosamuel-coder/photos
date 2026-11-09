<?php

namespace App\Services;

use App\Models\Classe;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\Storage;

class CardGenerationService
{
    private $cardWidth = 85;
    private $cardHeight = 55;
    private $marginX = 14;
    private $marginY = 1.7;
    private $cardsPerRow = 2;
    private $cardsPerColumn = 5;

    public function generateCards(Classe $classe): string
    {
        $etudiants = $classe->etudiants()
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->orderBy('nom')
            ->get();

        if ($etudiants->isEmpty()) {
            throw new \Exception("Aucun étudiant avec photo trouvé pour cette classe.");
        }

        $pdf = new Fpdf('P', 'mm', 'A4');
        $cardsPerPage = $this->cardsPerRow * $this->cardsPerColumn;
        
        // Générer les rectos
        $this->generateRectoPages($pdf, $etudiants, $cardsPerPage);
        
        // Générer les versos (dans le même PDF)
        $this->generateVersoPages($pdf, $etudiants, $cardsPerPage);

        $fileName = "ID_Cards_{$classe->nom}_" . date('Y-m-d_His') . ".pdf";
        $filePath = storage_path("app/public/generated/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $pdf->Output('F', $filePath);

        return $filePath;
    }

    public function generateGlobalCards($classes, $outputPath): void
    {
        $pdf = new Fpdf('P', 'mm', 'A4');
        $cardsPerPage = $this->cardsPerRow * $this->cardsPerColumn;
        
        // Collecter tous les étudiants de toutes les classes
        $allStudents = collect();
        foreach ($classes as $classe) {
            $etudiants = $classe->etudiants()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->orderBy('nom')
                ->get();
            
            $allStudents = $allStudents->merge($etudiants);
        }
        
        if ($allStudents->isEmpty()) {
            throw new \Exception("Aucun étudiant avec photo trouvé dans les classes sélectionnées.");
        }
        
        // Générer tous les rectos
        $this->generateRectoPages($pdf, $allStudents, $cardsPerPage);
        
        // Générer tous les versos
        $this->generateVersoPages($pdf, $allStudents, $cardsPerPage);
        
        $pdf->Output('F', $outputPath);
        
        \Log::info("PDF global créé avec succès : {$outputPath} ({$allStudents->count()} cartes)");
    }

    private function generateRectoPages($pdf, $etudiants, $cardsPerPage)
    {
        $cardsCount = 0;

        foreach ($etudiants as $etudiant) {
            if ($cardsCount % $cardsPerPage == 0) {
                $pdf->AddPage();
            }

            $position = $this->getCardPosition($cardsCount, $cardsPerPage);
            $this->drawCardRecto($pdf, $etudiant, $position['x'], $position['y']);
            $cardsCount++;
        }
    }

    private function generateVersoPages($pdf, $etudiants, $cardsPerPage)
    {
        $cardsCount = 0;

        foreach ($etudiants as $etudiant) {
            if ($cardsCount % $cardsPerPage == 0) {
                $pdf->AddPage();
            }

            $position = $this->getCardPosition($cardsCount, $cardsPerPage, true); // true = verso (miroir)
            $this->drawCardVerso($pdf, $etudiant, $position['x'], $position['y']);
            $cardsCount++;
        }
    }

    private function getCardPosition($cardsCount, $cardsPerPage, $isVerso = false)
    {
        $row = floor(($cardsCount % $cardsPerPage) / $this->cardsPerRow);
        $col = ($cardsCount % $cardsPerPage) % $this->cardsPerRow;

        // Pour le verso, on inverse l'ordre horizontal pour faciliter l'impression recto-verso
        if ($isVerso) {
            $col = ($this->cardsPerRow - 1) - $col;
        }

        $x = $this->marginX + ($col * ($this->cardWidth + $this->marginX));
        $y = $this->marginY + ($row * ($this->cardHeight + $this->marginY));

        return ['x' => $x, 'y' => $y];
    }

    private function drawCardRecto($pdf, $etudiant, $x, $y)
    {
        // Fond blanc
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $this->cardWidth, $this->cardHeight, 'F');

        // Image de fond (RECTO)
        $backgroundPath = public_path('assets/images/carte-recto.png');
        if (file_exists($backgroundPath)) {
            $pdf->Image($backgroundPath, $x, $y, $this->cardWidth, $this->cardHeight);
        } else {
            // Bordure de carte si pas d'image de fond
            $pdf->SetDrawColor(0, 100, 0);
            $pdf->SetLineWidth(0.5);
            $pdf->Rect($x, $y, $this->cardWidth, $this->cardHeight);
        }

        // Photo de l'étudiant
        $photoPath = $etudiant->photoPath;
        if (file_exists($photoPath)) {
            $pdf->Image($photoPath, $x + 4.5, $y + 19.2, 27, 29);
        }

        // Informations textuelles
        $this->addCardText($pdf, $etudiant, $x, $y);
    }

    private function drawCardVerso($pdf, $etudiant, $x, $y)
    {
        // Fond blanc
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $this->cardWidth, $this->cardHeight, 'F');

        // Image de fond (VERSO)
        $backgroundPath = public_path('assets/images/carte-verso.png');
        if (file_exists($backgroundPath)) {
            $pdf->Image($backgroundPath, $x, $y, $this->cardWidth, $this->cardHeight);
        } else {
            // Bordure de carte si pas d'image de fond
            $pdf->SetDrawColor(0, 100, 0);
            $pdf->SetLineWidth(0.5);
            $pdf->Rect($x, $y, $this->cardWidth, $this->cardHeight);
            
            // Texte de démonstration au centre
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(0, 100, 0);
            $pdf->SetXY($x, $y + ($this->cardHeight / 2) - 5);
            $pdf->Cell($this->cardWidth, 10, $this->convert('VERSO'), 0, 0, 'C');
        }
    }

    private function addCardText($pdf, $etudiant, $x, $y)
    {
        $fields = [
            [
                'label' => 'Nom & Prénoms',
                'labelEn' => '/ Names & First Names',
                'value' => strtoupper($etudiant->nom),
                'yPos' => 12,
                'maxWidth' => 45,
                'fontSize' => 8
            ],
            [
                'label' => 'Né(e) le',
                'labelEn' => '/ Born on',
                'value' => $this->formatDate($etudiant->date_naissance),
                'yPos' => 18.8,
                'maxWidth' => 45,
                'fontSize' => 8
            ],
            [
                'label' => 'À',
                'labelEn' => '/ at',
                'value' => strtoupper($etudiant->lieu_naissance),
                'yPos' => 23.7,
                'maxWidth' => 45,
                'fontSize' => 7
            ],
            [
                'label' => 'Sexe',
                'labelEn' => '/ Sex',
                'value' => $etudiant->sexe == 'F' ? 'Féminin' : 'Masculin',
                'yPos' => 28.6,
                'maxWidth' => 45,
                'fontSize' => 8
            ],
            [
                'label' => 'Classe',
                'labelEn' => '/ Class',
                'value' => strtoupper($etudiant->classe->nom),
                'yPos' => 33.5,
                'maxWidth' => 45,
                'fontSize' => 8
            ],
            [
                'label' => 'ID NATIONAL',
                'labelEn' => '',
                'value' => strtoupper($etudiant->matricule_national),
                'yPos' => 38.4,
                'maxWidth' => 45,
                'fontSize' => 8
            ],
            [
                'label' => 'Contact Parent',
                'labelEn' => '/ Tuteur',
                'value' => $etudiant->contact_parent ?? 'RAS',
                'yPos' => 43,
                'maxWidth' => 45,
                'fontSize' => 7
            ],
        ];

        foreach ($fields as $field) {
            $this->renderField($pdf, $field, $x, $y);
        }
    }

    private function renderField($pdf, $field, $x, $y)
    {
        // Label principal
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x + 38, $y + $field['yPos']);
        $pdf->Cell(40, 5, $this->convert($field['label']), 0, 0, 'L');

        // Label en anglais
        if (!empty($field['labelEn'])) {
            $pdf->SetFont('Arial', 'I', 6);
            $pdf->SetXY($x + 54, $y + $field['yPos']);
            $pdf->Cell(40, 5, $this->convert($field['labelEn']), 0, 0, 'L');
        }

        // Valeur avec gestion des longs textes
        $this->renderMultilineValue(
            $pdf, 
            $field['value'], 
            $x + 38, 
            $y + $field['yPos'] + 2.5,
            $field['maxWidth'],
            $field['fontSize']
        );
    }

    private function renderMultilineValue($pdf, $text, $x, $y, $maxWidth, $fontSize)
    {
        $pdf->SetFont('Arial', 'B', $fontSize);
        $pdf->SetTextColor(25, 64, 190);
        
        $convertedText = $this->convert($text);
        
        // Calculer la largeur du texte
        $textWidth = $pdf->GetStringWidth($convertedText);
        
        if ($textWidth <= $maxWidth) {
            // Le texte tient sur une ligne
            $pdf->SetXY($x, $y);
            $pdf->Cell($maxWidth, 5, $convertedText, 0, 0, 'L');
        } else {
            // Le texte doit être coupé sur plusieurs lignes
            $words = explode(' ', $convertedText);
            $line = '';
            $lineHeight = 3;
            $currentY = $y;
            
            foreach ($words as $word) {
                $testLine = $line . ($line ? ' ' : '') . $word;
                $testWidth = $pdf->GetStringWidth($testLine);
                
                if ($testWidth > $maxWidth && $line !== '') {
                    // Afficher la ligne actuelle
                    $pdf->SetXY($x, $currentY);
                    $pdf->Cell($maxWidth, $lineHeight, $line, 0, 0, 'L');
                    $line = $word;
                    $currentY += $lineHeight;
                } else {
                    $line = $testLine;
                }
            }
            
            // Afficher la dernière ligne
            if ($line !== '') {
                $pdf->SetXY($x, $currentY);
                $pdf->Cell($maxWidth, $lineHeight, $line, 0, 0, 'L');
            }
        }
    }

    private function convert($text)
    {
        return mb_convert_encoding($text, 'windows-1252', 'UTF-8');
    }

    private function formatDate($date)
    {
        if (empty($date)) {
            return 'N/A';
        }

        try {
            // Si c'est déjà au bon format, le retourner tel quel
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                return $date;
            }

            // Convertir différents formats vers DD/MM/YYYY
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return $date;
            }

            return date('d/m/Y', $timestamp);
        } catch (\Exception $e) {
            return $date;
        }
    }
}