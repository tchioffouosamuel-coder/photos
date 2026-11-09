<?php
namespace App\Services;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\ImportHistory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExcelImportService
{
    public function importFromExcel($file, $classeId)
    {
        $classe = Classe::findOrFail($classeId);
        
        $importHistory = ImportHistory::create([
            'classe_id' => $classeId,
            'file_name' => $file->getClientOriginalName(),
            'status' => 'processing',
            'imported_by' => auth()->id() ?? 'system'
        ]);
        
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Trouver la ligne de début des données (après "N°")
            $startRow = $this->findDataStartRow($worksheet);
            
            if (!$startRow) {
                throw new \Exception("Format de fichier invalide. Ligne de début introuvable.");
            }
            
            $highestRow = $worksheet->getHighestRow();
            $imported = 0;
            $failed = 0;
            $errors = [];
            
            DB::beginTransaction();
            
            for ($row = $startRow + 1; $row <= $highestRow; $row++) {
                try {
                    $data = $this->extractRowData($worksheet, $row);
                    
                    if (empty($data['nom'])) {
                        continue; // Ligne vide
                    }
                    
                    Etudiant::updateOrCreate(
                        [
                            'matricule_national' => $data['matricule_national'],
                            'classe_id' => $classeId
                        ],
                        $data
                    );
                    
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Ligne $row: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            // Mettre à jour les effectifs
            $classe->updateEffectifs();
            
            $importHistory->update([
                'total_rows' => $highestRow - $startRow,
                'imported_rows' => $imported,
                'failed_rows' => $failed,
                'status' => 'completed',
                'error_log' => $errors
            ]);
            
            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $importHistory->update([
                'status' => 'failed',
                'error_log' => [$e->getMessage()]
            ]);
            
            throw $e;
        }
    }
    
    private function findDataStartRow($worksheet)
    {
        for ($row = 1; $row <= 20; $row++) {
            $cellValue = $worksheet->getCell("A$row")->getValue();
            if ($cellValue === 'N°' || $cellValue === 'N' || strtolower($cellValue) === 'n°') {
                return $row;
            }
        }
        return null;
    }
    
    private function extractRowData($worksheet, $row)
    {
        return [
            'matricule_national' => $this->getCellValue($worksheet, "B$row"),
            'matricule_interne' => $this->getCellValue($worksheet, "C$row"),
            'nom' => strtoupper($this->getCellValue($worksheet, "D$row")),
            'prenoms' => $this->getCellValue($worksheet, "D$row"), // Nom complet dans colonne D
            'date_naissance' => $this->parseDate($this->getCellValue($worksheet, "E$row")),
            'lieu_naissance' => strtoupper($this->getCellValue($worksheet, "F$row")),
            'sexe' => strtoupper($this->getCellValue($worksheet, "G$row")),
            'statut' => strtoupper($this->getCellValue($worksheet, "H$row")) ?: 'N',
        ];
    }
    
    private function getCellValue($worksheet, $cell)
    {
        return trim($worksheet->getCell($cell)->getValue() ?? '');
    }
    
    private function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        
        try {
            // Format DD/MM/YYYY
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateString, $matches)) {
                return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
            }
            
            // Format timestamp Excel
            if (is_numeric($dateString)) {
                $unix = ($dateString - 25569) * 86400;
                return date('Y-m-d', $unix);
            }
            
            return $dateString;
        } catch (\Exception $e) {
            return null;
        }
    }
}