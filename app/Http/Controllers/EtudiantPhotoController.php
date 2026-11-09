<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EtudiantController extends Controller
{
    /**
     * Upload photo - Optimisé pour PC et Mobile
     */
    public function uploadPhoto(Request $request, $etudiant)
    {
        try {
            // Log pour debugging
            Log::info('Upload photo request', [
                'etudiant_id' => $etudiant,
                'has_file' => $request->hasFile('photo'),
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Validation avec messages personnalisés
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max pour mobile
            ], [
                'photo.required' => 'Veuillez sélectionner une photo',
                'photo.image' => 'Le fichier doit être une image',
                'photo.mimes' => 'Format accepté: JPG, PNG',
                'photo.max' => 'La photo ne doit pas dépasser 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $etudiant = Etudiant::findOrFail($etudiant);
            $classe = $etudiant->classe;

            if (!$classe) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'élève n\'est pas associé à une classe'
                ], 400);
            }

            // Supprimer l'ancienne photo
            if ($etudiant->photo) {
                $this->deleteOldPhoto($etudiant, $classe);
            }

            // Générer le nom de fichier
            $filename = $this->generatePhotoFilename($etudiant);
            $directory = "photos/{$classe->nom}";
            
            // S'assurer que le dossier existe dans storage/app/public
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Stocker le fichier dans storage/app/public
            $path = $request->file('photo')->storeAs(
                $directory,
                $filename,
                'public'
            );

            // Synchroniser vers public/storage
            $this->syncPhotoToPublic($path);

            // Mettre à jour la base de données
            $etudiant->photo = $filename;
            $etudiant->save();

            // Construire l'URL correcte
            $photoUrl = $this->getPhotoUrl($path);

            Log::info('Photo uploaded successfully', [
                'etudiant_id' => $etudiant->id,
                'filename' => $filename,
                'path' => $path,
                'url' => $photoUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo enregistrée avec succès',
                'photo_url' => $photoUrl,
                'filename' => $filename,
                'etudiant_id' => $etudiant->id
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Student not found', ['etudiant_id' => $etudiant]);
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Photo upload error', [
                'etudiant_id' => $etudiant,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la photo',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur serveur'
            ], 500);
        }
    }

    /**
     * Capture photo depuis webcam/camera mobile - Optimisé
     */
    public function capturePhoto(Request $request, $etudiant)
    {
        try {
            Log::info('Capture photo request', [
                'etudiant_id' => $etudiant,
                'has_file' => $request->hasFile('photo')
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            ], [
                'photo.required' => 'Aucune photo capturée',
                'photo.image' => 'Le fichier doit être une image',
                'photo.mimes' => 'Format accepté: JPG, PNG',
                'photo.max' => 'La photo ne doit pas dépasser 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $etudiant = Etudiant::findOrFail($etudiant);
            $classe = $etudiant->classe;

            if (!$classe) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'élève n\'est pas associé à une classe'
                ], 400);
            }

            // Supprimer l'ancienne photo
            if ($etudiant->photo) {
                $this->deleteOldPhoto($etudiant, $classe);
            }

            // Générer le nom de fichier
            $filename = $this->generatePhotoFilename($etudiant);
            $directory = "photos/{$classe->nom}";
            
            // S'assurer que le dossier existe
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Stocker la photo capturée
            $path = $request->file('photo')->storeAs(
                $directory,
                $filename,
                'public'
            );

            // Synchroniser vers public/storage
            $this->syncPhotoToPublic($path);

            // Mettre à jour la base de données
            $etudiant->photo = $filename;
            $etudiant->save();

            // Construire l'URL
            $photoUrl = $this->getPhotoUrl($path);

            Log::info('Photo captured successfully', [
                'etudiant_id' => $etudiant->id,
                'filename' => $filename
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo capturée et enregistrée avec succès',
                'photo_url' => $photoUrl,
                'filename' => $filename,
                'etudiant_id' => $etudiant->id
            ], 200);

        } catch (\Exception $e) {
            Log::error('Photo capture error', [
                'etudiant_id' => $etudiant,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la capture de la photo',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur serveur'
            ], 500);
        }
    }

    /**
     * Supprimer l'ancienne photo
     */
    private function deleteOldPhoto(Etudiant $etudiant, $classe)
    {
        try {
            $oldPhotoPath = "photos/{$classe->nom}/{$etudiant->photo}";
            
            // Supprimer de storage/app/public
            if (Storage::disk('public')->exists($oldPhotoPath)) {
                Storage::disk('public')->delete($oldPhotoPath);
                Log::info('Old photo deleted from storage', ['path' => $oldPhotoPath]);
            }
            
            // Supprimer aussi de public/storage (copie physique)
            $oldPublicPath = public_path("storage/{$oldPhotoPath}");
            if (file_exists($oldPublicPath)) {
                @unlink($oldPublicPath);
                Log::info('Old photo deleted from public', ['path' => $oldPublicPath]);
            }
        } catch (\Exception $e) {
            Log::warning('Error deleting old photo', [
                'error' => $e->getMessage(),
                'photo' => $etudiant->photo
            ]);
        }
    }

    /**
     * Synchroniser la photo vers public/storage
     */
    private function syncPhotoToPublic($relativePath)
    {
        try {
            $source = storage_path('app/public/' . $relativePath);
            $dest = public_path('storage/' . $relativePath);
            
            // Créer le dossier de destination s'il n'existe pas
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            // Copier le fichier
            if (file_exists($source)) {
                copy($source, $dest);
                chmod($dest, 0644);
                
                Log::info('Photo synced to public', [
                    'source' => $source,
                    'dest' => $dest
                ]);
                
                return true;
            }
            
            Log::warning('Source file not found for sync', ['source' => $source]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('Error syncing photo to public', [
                'path' => $relativePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir l'URL correcte de la photo
     */
    private function getPhotoUrl($path)
    {
        // Utiliser asset() pour générer l'URL correcte automatiquement
        // Cela s'adapte automatiquement à l'environnement (local/production)
        return asset('storage/' . $path);
    }

    /**
     * Générer un nom de fichier standardisé
     */
    private function generatePhotoFilename(Etudiant $etudiant)
    {
        $nom = $this->cleanString($etudiant->nom);
        $prenoms = $this->cleanString($etudiant->prenoms);
        
        $identifier = $etudiant->matricule_national 
            ? $this->cleanString($etudiant->matricule_national)
            : 'ID' . $etudiant->id;

        return "{$nom}_{$prenoms}_{$identifier}.jpg";
    }

    /**
     * Nettoyer une chaîne pour un nom de fichier
     */
    private function cleanString($string)
    {
        // Supprimer les accents
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        
        // Supprimer les caractères spéciaux et espaces
        $string = preg_replace('/[^A-Za-z0-9]/', '_', $string);
        
        // Supprimer les underscores multiples
        $string = preg_replace('/_+/', '_', $string);
        
        // Trim underscores
        $string = trim($string, '_');
        
        return strtoupper($string);
    }

    /**
     * Delete student photo
     */
    public function deletePhoto($etudiant)
    {
        try {
            $etudiant = Etudiant::findOrFail($etudiant);
            $classe = $etudiant->classe;

            if (!$etudiant->photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune photo à supprimer'
                ], 400);
            }

            $this->deleteOldPhoto($etudiant, $classe);

            $etudiant->photo = null;
            $etudiant->save();

            Log::info('Photo deleted', ['etudiant_id' => $etudiant->id]);

            return response()->json([
                'success' => true,
                'message' => 'Photo supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting photo', [
                'etudiant_id' => $etudiant,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la photo',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur serveur'
            ], 500);
        }
    }

    // ... Autres méthodes CRUD (create, store, edit, update, destroy) ...
}