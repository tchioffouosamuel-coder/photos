<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EtudiantPhotoController extends Controller
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

    /*
     |--------------------------------------------------------------------------
     | IMPORT GROUPE DE PHOTOS
     |--------------------------------------------------------------------------
     |
     | Recoit un lot de fichiers et les attribue automatiquement aux eleves
     | d'une classe en se basant sur le nom du fichier. Sont reconnus :
     |
     |     MATRICULE.jpg                      (matricule national ou interne)
     |     NOM_PRENOMS.jpg
     |     NOM_PRENOMS_MATRICULE.jpg          (convention de ce logiciel)
     |     ID12.jpg                           (identifiant interne)
     |
     | Les separateurs, les accents et la casse sont ignores : "n'diaye jean.JPG"
     | et "NDIAYE_JEAN.jpg" visent le meme eleve.
     |
     | Chaque fichier est traite independamment : un fichier non reconnu
     | n'empeche pas les autres d'etre enregistres. La reponse detaille le sort
     | de chacun pour que l'utilisateur sache quoi corriger.
     |
     | Attention : PHP limite le nombre de fichiers par requete
     | (max_file_uploads, 20 par defaut). L'interface envoie donc des lots.
     */
    public function bulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classe_id' => 'required|integer|exists:classes,id',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'remplacer' => 'nullable|boolean',
        ], [
            'classe_id.required' => 'La classe de destination est obligatoire',
            'classe_id.exists' => 'Cette classe est introuvable',
            'photos.required' => 'Veuillez selectionner au moins une photo',
            'photos.*.image' => 'Un des fichiers n\'est pas une image',
            'photos.*.mimes' => 'Formats acceptes : JPG, PNG',
            'photos.*.max' => 'Chaque photo doit faire moins de 10 Mo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $classe = \App\Models\Classe::find($request->input('classe_id'));

        if (! $classe) {
            return response()->json([
                'success' => false,
                'message' => 'Cette classe est introuvable',
            ], 404);
        }

        $remplacer = $request->boolean('remplacer');
        $fichiers = $request->file('photos');
        $index = $this->buildMatchIndex($classe);

        $attribues = [];
        $ignores = [];
        $inconnus = [];
        $echecs = [];

        $directory = self::photoDirectory($classe);

        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        foreach ($fichiers as $fichier) {
            $nomOriginal = $fichier->getClientOriginalName();
            $cle = $this->matchKey(pathinfo($nomOriginal, PATHINFO_FILENAME));
            $trouves = $index[$cle] ?? [];

            if (count($trouves) === 0) {
                $inconnus[] = [
                    'fichier' => $nomOriginal,
                    'raison' => 'Aucun eleve de cette classe ne correspond a ce nom de fichier',
                ];
                continue;
            }

            if (count($trouves) > 1) {
                $ignores[] = [
                    'fichier' => $nomOriginal,
                    'raison' => 'Plusieurs eleves correspondent (' . count($trouves) . ') : renommez le fichier avec le matricule',
                ];
                continue;
            }

            /** @var Etudiant $etudiant */
            $etudiant = $trouves[0];

            if ($etudiant->photo && ! $remplacer) {
                $ignores[] = [
                    'fichier' => $nomOriginal,
                    'eleve' => $this->libelleEleve($etudiant),
                    'raison' => 'Cet eleve a deja une photo',
                ];
                continue;
            }

            try {
                if ($etudiant->photo) {
                    $this->deleteOldPhoto($etudiant, $classe);
                }

                $filename = $this->generatePhotoFilename($etudiant);
                $path = $fichier->storeAs($directory, $filename, 'public');

                if (! $path) {
                    throw new \RuntimeException('Le fichier n\'a pas pu etre ecrit sur le disque');
                }

                $this->syncPhotoToPublic($path);

                $etudiant->photo = $filename;
                $etudiant->save();

                $attribues[] = [
                    'fichier' => $nomOriginal,
                    'eleve' => $this->libelleEleve($etudiant),
                    'etudiant_id' => $etudiant->id,
                    'photo_url' => $this->getPhotoUrl($path),
                ];
            } catch (\Throwable $e) {
                Log::error('[photos] import groupe : echec sur un fichier', [
                    'classe_id' => $classe->id,
                    'etudiant_id' => $etudiant->id,
                    'fichier' => $nomOriginal,
                    'error' => $e->getMessage(),
                ]);

                $echecs[] = [
                    'fichier' => $nomOriginal,
                    'eleve' => $this->libelleEleve($etudiant),
                    'raison' => config('app.debug')
                        ? $e->getMessage()
                        : 'Erreur serveur lors de l\'enregistrement',
                ];
            }
        }

        Log::info('[photos] import groupe termine', [
            'classe_id' => $classe->id,
            'recus' => count($fichiers),
            'attribues' => count($attribues),
            'ignores' => count($ignores),
            'inconnus' => count($inconnus),
            'echecs' => count($echecs),
        ]);

        return response()->json([
            'success' => count($echecs) === 0,
            'message' => $this->resumeImport(count($attribues), count($ignores), count($inconnus), count($echecs)),
            'resume' => [
                'recus' => count($fichiers),
                'attribues' => count($attribues),
                'ignores' => count($ignores),
                'inconnus' => count($inconnus),
                'echecs' => count($echecs),
            ],
            'details' => [
                'attribues' => $attribues,
                'ignores' => $ignores,
                'inconnus' => $inconnus,
                'echecs' => $echecs,
            ],
            'max_fichiers_par_requete' => (int) ini_get('max_file_uploads'),
        ], 200);
    }

    /**
     * Index des cles de rapprochement -> eleves de la classe.
     *
     * Une cle peut viser plusieurs eleves (homonymes sans matricule) : le cas
     * est alors signale a l'utilisateur plutot que tranche au hasard.
     *
     * @return array<string, array<int, Etudiant>>
     */
    private function buildMatchIndex($classe): array
    {
        $index = [];

        $ajouter = function (?string $cle, Etudiant $etudiant) use (&$index) {
            $cle = $this->matchKey((string) $cle);

            if ($cle === '') {
                return;
            }

            foreach ($index[$cle] ?? [] as $deja) {
                if ($deja->id === $etudiant->id) {
                    return;
                }
            }

            $index[$cle][] = $etudiant;
        };

        $etudiants = $classe->etudiants()->get();

        foreach ($etudiants as $etudiant) {
            $ajouter($etudiant->matricule_national, $etudiant);
            $ajouter($etudiant->matricule_interne, $etudiant);
            $ajouter($etudiant->nom . '_' . $etudiant->prenoms, $etudiant);
            $ajouter($etudiant->prenoms . '_' . $etudiant->nom, $etudiant);
            $ajouter('ID' . $etudiant->id, $etudiant);
            $ajouter(pathinfo($this->generatePhotoFilename($etudiant), PATHINFO_FILENAME), $etudiant);
        }

        return $index;
    }

    /**
     * Cle de rapprochement d'un nom de fichier ou d'une donnee d'eleve.
     *
     * Volontairement distincte de cleanString(), qui sert a fabriquer des noms
     * de fichiers et remplace les separateurs par des underscores. Ici tous
     * les caracteres non alphanumeriques sont supprimes, afin que les ecritures
     * suivantes mènent a la meme cle :
     *
     *     N'DIAYE Amelie       ->  NDIAYEAMELIE
     *     ndiaye-amelie        ->  NDIAYEAMELIE
     *     N_DIAYE_AMELIE       ->  NDIAYEAMELIE
     *
     * C'est ce qui permet a l'utilisateur de nommer ses fichiers librement.
     */
    private function matchKey(string $valeur): string
    {
        $valeur = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valeur);

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $valeur));
    }

    /**
     * Libelle lisible d'un eleve, pour le compte rendu d'import.
     */
    private function libelleEleve(Etudiant $etudiant): string
    {
        return trim($etudiant->nom . ' ' . $etudiant->prenoms);
    }

    /**
     * Phrase de synthese du compte rendu d'import.
     */
    private function resumeImport(int $attribues, int $ignores, int $inconnus, int $echecs): string
    {
        if ($attribues === 0 && $ignores === 0 && $echecs === 0) {
            return 'Aucune photo n\'a pu etre rattachee : verifiez les noms de fichiers';
        }

        $parts = [];
        $parts[] = $attribues . ' photo' . ($attribues > 1 ? 's' : '') . ' attribuee' . ($attribues > 1 ? 's' : '');

        if ($ignores > 0) {
            $parts[] = $ignores . ' ignoree' . ($ignores > 1 ? 's' : '');
        }

        if ($inconnus > 0) {
            $parts[] = $inconnus . ' sans correspondance';
        }

        if ($echecs > 0) {
            $parts[] = $echecs . ' en echec';
        }

        return implode(', ', $parts);
    }

    /**
     * Dossier de stockage des photos d'une classe.
     */
    private static function photoDirectory($classe): string
    {
        return 'photos/' . $classe->nom;
    }

    // ... Autres méthodes CRUD (create, store, edit, update, destroy) ...
}