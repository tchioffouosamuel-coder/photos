<?php

namespace App\Http\Controllers;

use App\Models\CandidatLibre;
use App\Models\Classe;
use App\Services\UnifiedPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CandidatLibreController extends Controller
{
    private UnifiedPhotoService $photoService;

    public function __construct(UnifiedPhotoService $photoService)
    {
        $this->photoService = $photoService;
    }

    public function index(Classe $classe)
    {
        if (empty($classe->exam)) {
            return redirect()->route('classes.show', $classe)
                ->with('error', 'Cette classe n\'est pas une classe d\'examen');
        }

        $candidats = $classe->candidatsLibres()->orderBy('nom')->get();
        
        return view('candidats-libres.index', compact('classe', 'candidats'));
    }

    public function create(Classe $classe)
    {
        if (empty($classe->exam)) {
            return redirect()->route('classes.show', $classe)
                ->with('error', 'Cette classe n\'est pas une classe d\'examen');
        }

        return view('candidats-libres.create', compact('classe'));
    }

    public function store(Request $request, Classe $classe)
    {
        if (empty($classe->exam)) {
            return redirect()->route('classes.show', $classe)
                ->with('error', 'Cette classe n\'est pas une classe d\'examen');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'photo' => 'nullable|image|max:10240'
        ]);

        $validated['classe_id'] = $classe->id;

        if ($request->hasFile('photo')) {
            $filename = $this->generatePhotoFilename($validated['nom'], $classe);
            $directory = "photos_cl/{$classe->nom}";
            
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $path = $request->file('photo')->storeAs($directory, $filename, 'public');
            $this->syncPhotoToPublic($path);
            
            $validated['photo'] = $filename;
        }

        CandidatLibre::create($validated);

        return redirect()->route('candidats-libres.index', $classe)
            ->with('success', 'Candidat libre ajouté avec succès');
    }

    public function edit(Classe $classe, CandidatLibre $candidatLibre)
    {
        if ($candidatLibre->classe_id !== $classe->id) {
            abort(404);
        }

        return view('candidats-libres.edit', compact('classe', 'candidatLibre'));
    }

    public function update(Request $request, Classe $classe, CandidatLibre $candidatLibre)
    {
        if ($candidatLibre->classe_id !== $classe->id) {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'photo' => 'nullable|image|max:10240'
        ]);

        if ($request->hasFile('photo')) {
            if ($candidatLibre->photo) {
                $this->deletePhoto($candidatLibre, $classe);
            }

            $filename = $this->generatePhotoFilename($validated['nom'], $classe);
            $directory = "photos_cl/{$classe->nom}";
            
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $path = $request->file('photo')->storeAs($directory, $filename, 'public');
            $this->syncPhotoToPublic($path);
            
            $validated['photo'] = $filename;
        }

        $candidatLibre->update($validated);

        return redirect()->route('candidats-libres.index', $classe)
            ->with('success', 'Candidat libre mis à jour');
    }

    public function destroy(Classe $classe, CandidatLibre $candidatLibre)
    {
        if ($candidatLibre->classe_id !== $classe->id) {
            abort(404);
        }

        if ($candidatLibre->photo) {
            $this->deletePhoto($candidatLibre, $classe);
        }

        $candidatLibre->delete();

        return redirect()->route('candidats-libres.index', $classe)
            ->with('success', 'Candidat libre supprimé');
    }

    /**
     * Générer les photos 4x4 pour candidats libres
     */
    public function generate4x4(Classe $classe)
    {
        try {
            $pdfPath = $this->photoService->generate4x4Photos($classe, true);
            
            return response()->download($pdfPath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération 4x4 CL: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Générer les photos PDF pour candidats libres
     */
    public function generatePDF(Classe $classe)
    {
        try {
            $zipPath = $this->photoService->generateProcessedPhotos($classe, true);
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF CL: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function uploadPhoto(Request $request, $candidatLibre)
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $candidatLibre = CandidatLibre::findOrFail($candidatLibre);
            $classe = $candidatLibre->classe;

            if ($candidatLibre->photo) {
                $this->deletePhoto($candidatLibre, $classe);
            }

            $filename = $this->generatePhotoFilename($candidatLibre->nom, $classe);
            $directory = "photos_cl/{$classe->nom}";
            
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $path = $request->file('photo')->storeAs($directory, $filename, 'public');
            $this->syncPhotoToPublic($path);

            $candidatLibre->photo = $filename;
            $candidatLibre->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo enregistrée',
                'photo_url' => asset('storage/' . $path)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Upload error CL: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    public function capturePhoto(Request $request, $candidatLibre)
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $candidatLibre = CandidatLibre::findOrFail($candidatLibre);
            $classe = $candidatLibre->classe;

            if ($candidatLibre->photo) {
                $this->deletePhoto($candidatLibre, $classe);
            }

            $filename = $this->generatePhotoFilename($candidatLibre->nom, $classe);
            $directory = "photos_cl/{$classe->nom}";
            
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $path = $request->file('photo')->storeAs($directory, $filename, 'public');
            $this->syncPhotoToPublic($path);

            $candidatLibre->photo = $filename;
            $candidatLibre->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo capturée',
                'photo_url' => asset('storage/' . $path)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Capture error CL: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    // Helpers
    private function generatePhotoFilename($nom, $classe)
    {
        $nom = $this->cleanString($nom);
        $timestamp = time();
        return "CL_{$nom}_{$timestamp}.jpg";
    }

    private function cleanString($string)
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $string = preg_replace('/[^A-Za-z0-9]/', '_', $string);
        $string = preg_replace('/_+/', '_', $string);
        return strtoupper(trim($string, '_'));
    }

    private function deletePhoto($candidatLibre, $classe)
    {
        try {
            $photoPath = "photos_cl/{$classe->nom}/{$candidatLibre->photo}";
            
            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            $publicPath = public_path("storage/{$photoPath}");
            if (file_exists($publicPath)) {
                @unlink($publicPath);
            }
        } catch (\Exception $e) {
            Log::warning('Delete error: ' . $e->getMessage());
        }
    }

    private function syncPhotoToPublic($relativePath)
    {
        try {
            $source = storage_path('app/public/' . $relativePath);
            $dest = public_path('storage/' . $relativePath);
            
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            if (file_exists($source)) {
                copy($source, $dest);
                chmod($dest, 0644);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Sync error: ' . $e->getMessage());
            return false;
        }
    }
}