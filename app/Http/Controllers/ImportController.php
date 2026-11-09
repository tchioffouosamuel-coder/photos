<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\ImportHistory;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;

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
    
    public function template()
    {
        return response()->download(
            public_path('templates/liste_eleves_template.xlsx'),
            'modele_liste_eleves.xlsx'
        );
    }
}