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

                    $existant = $this->findExisting($data, $classeId);

                    if ($existant) {
                        $existant->fill($data)->save();
                    } else {
                        Etudiant::create($data + ['classe_id' => $classeId]);
                    }

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
        [$nom, $prenoms] = $this->splitFullName($this->getCellValue($worksheet, "D$row"));

        $matriculeNational = $this->getCellValue($worksheet, "B$row");
        $matriculeInterne = $this->getCellValue($worksheet, "C$row");

        return [
            /*
             | Un matricule absent est enregistre a null et non en chaine vide :
             | la contrainte d'unicite porte sur (matricule_national, classe_id),
             | et MySQL tolere plusieurs NULL mais une seule chaine vide. Des
             | eleves sans matricule s'ecrasaient donc les uns les autres.
             */
            'matricule_national' => $matriculeNational !== '' ? $matriculeNational : null,
            'matricule_interne' => $matriculeInterne !== '' ? $matriculeInterne : null,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'date_naissance' => $this->parseDate($this->getCellValue($worksheet, "E$row")),
            'lieu_naissance' => mb_strtoupper($this->getCellValue($worksheet, "F$row")),
            'sexe' => mb_strtoupper($this->getCellValue($worksheet, "G$row")),
            'statut' => mb_strtoupper($this->getCellValue($worksheet, "H$row")) ?: 'N',
        ];
    }

    /**
     * Separe le contenu de la colonne D en nom de famille et prenoms.
     *
     * La colonne porte le nom complet, selon l'usage des listes scolaires :
     * nom de famille en MAJUSCULES, prenoms en casse normale. Les mots en
     * capitales de tete forment donc le nom, le reste les prenoms, ce qui
     * traite correctement les noms composes :
     *
     *     "MBALLA Paul Eric"           -> MBALLA            / Paul Eric
     *     "NGO BALEPA Amelie Therese"  -> NGO BALEPA        / Amelie Therese
     *     "N'DIAYE Amelie"             -> N'DIAYE           / Amelie
     *
     * Sans indice de casse exploitable, on retombe sur la convention la plus
     * repandue : le premier mot est le nom.
     *
     *     "Mballa Paul"                -> MBALLA            / Paul
     *     "MBALLA PAUL"                -> MBALLA            / PAUL
     *
     * Auparavant les deux champs recevaient la meme chaine complete, ce qui
     * affichait et imprimait le nom en double sur les cartes.
     *
     * @return array{0: string, 1: string} [nom, prenoms]
     */
    private function splitFullName($complet)
    {
        $complet = preg_replace('/\s+/u', ' ', trim((string) $complet));

        if ($complet === '') {
            return ['', ''];
        }

        $mots = explode(' ', $complet);

        if (count($mots) === 1) {
            return [mb_strtoupper($mots[0]), ''];
        }

        /* Mots en capitales de tete, en laissant toujours au moins un prenom */
        $nom = [];
        foreach ($mots as $mot) {
            if (count($nom) >= count($mots) - 1 || ! $this->isUpperCaseWord($mot)) {
                break;
            }
            $nom[] = $mot;
        }

        if ($nom === []) {
            $nom = [array_shift($mots)];
            $prenoms = $mots;
        } else {
            $prenoms = array_slice($mots, count($nom));
        }

        return [
            mb_strtoupper(implode(' ', $nom)),
            implode(' ', $prenoms),
        ];
    }

    /**
     * Le mot est-il ecrit tout en capitales ? (apostrophes et traits d'union
     * admis : N'DIAYE, NGO-BALEPA)
     */
    private function isUpperCaseWord($mot)
    {
        if (! preg_match('/\p{L}/u', $mot)) {
            return false;
        }

        return mb_strtoupper($mot) === $mot;
    }

    /**
     * Retrouve l'eleve deja enregistre que cette ligne doit mettre a jour.
     *
     * Le matricule national reste le critere de reference. En son absence, on
     * se rabat sur l'identite complete, plutot que sur un matricule vide : la
     * contrainte d'unicite portant sur (matricule_national, classe_id), toutes
     * les lignes sans matricule convergeaient vers un seul et meme eleve, qui
     * etait reecrit a chaque ligne.
     *
     * La date est comparee avec whereDate et non par egalite stricte : le cast
     * "date" du modele enregistre la valeur au format Y-m-d H:i:s, qui ne
     * correspond jamais a la chaine Y-m-d lue dans le fichier.
     *
     * @return \App\Models\Etudiant|null
     */
    private function findExisting(array $data, $classeId)
    {
        if (! empty($data['matricule_national'])) {
            return Etudiant::where('classe_id', $classeId)
                ->where('matricule_national', $data['matricule_national'])
                ->first();
        }

        $requete = Etudiant::where('classe_id', $classeId)
            ->where('nom', $data['nom'])
            ->where('prenoms', $data['prenoms']);

        if (! empty($data['date_naissance'])) {
            $requete->whereDate('date_naissance', $data['date_naissance']);
        } else {
            $requete->whereNull('date_naissance');
        }

        return $requete->first();
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