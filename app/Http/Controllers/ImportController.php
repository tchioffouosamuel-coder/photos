<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\ImportHistory;
use App\Services\ExcelImportService;
use App\Services\ExcelTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    private $importService;
    
    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }
    
    public function index()
    {
        $classes = Classe::orderBy('nom')->get();
        $history = ImportHistory::with('classe')->latest()->paginate(10);
        
        return view('import.index', compact('classes', 'history'));
    }
    
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'classe_id' => 'required|exists:classes,id'
        ]);
        
        try {
            $result = $this->importService->importFromExcel(
                $request->file('excel_file'),
                $request->classe_id
            );
            
            $message = "Importation terminée : {$result['imported']} élèves importés";
            if ($result['failed'] > 0) {
                $message .= ", {$result['failed']} échecs";
            }
            
            return redirect()->route('import.index')->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->route('import.index')->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
    
    /**
     * Telechargement du modele de liste d'eleves.
     *
     * Le classeur est genere a la demande plutot que lu dans public/templates/ :
     * ce fichier n'etait livre sur aucun environnement, et la route repondait
     * donc une erreur 500. Le generer garantit aussi qu'il reste conforme a ce
     * que ExcelImportService sait relire.
     */
    public function template(ExcelTemplateService $templates)
    {
        $nom = 'modele_liste_eleves_' . now()->format('Y-m-d') . '.xlsx';

        try {
            $contenu = $templates->build();
        } catch (\Throwable $e) {
            Log::error('[import] generation du modele impossible', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('import.index')
                ->with('error', 'Le modele n\'a pas pu etre genere. Reessayez ou signalez l\'incident.');
        }

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nom . '"',
            'Content-Length' => (string) strlen($contenu),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}