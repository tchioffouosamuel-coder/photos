<?php

namespace App\Services;

use App\Models\Etudiant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/*
 | Gestion des fichiers photos sur le disque.
 |
 | Les photos sont rangees par nom de classe :
 |     storage/app/public/photos/{classe}/{fichier}        (eleves)
 |     storage/app/public/photos_cl/{classe}/{fichier}     (candidats libres)
 |
 | Le dossier public/storage de cette installation est un vrai dossier et non
 | un lien symbolique : chaque operation est donc repercutee sur la copie
 | servie par le serveur web, comme le fait deja EtudiantPhotoController.
 |
 | Consequence importante : le nom de la classe fait partie du chemin. Tout
 | changement de classe (transfert d'un eleve, renommage d'une classe) doit
 | passer par ce service, sinon les photos deviennent introuvables.
 */
class PhotoStorageService
{
    public const STUDENT_ROOT = 'photos';
    public const CANDIDATE_ROOT = 'photos_cl';

    /**
     * Deplace la photo d'un eleve d'une classe vers une autre.
     *
     * @return string|null Le nom de fichier dans la classe de destination
     *                     (il peut differer en cas de collision), ou null si
     *                     l'eleve n'a pas de photo exploitable.
     */
    public function moveStudentPhoto(Etudiant $etudiant, string $fromClassName, string $toClassName): ?string
    {
        if (empty($etudiant->photo) || $fromClassName === $toClassName) {
            return $etudiant->photo ?: null;
        }

        $source = self::STUDENT_ROOT . "/{$fromClassName}/{$etudiant->photo}";

        if (! $this->exists($source)) {
            Log::warning('[photos] fichier source introuvable lors du transfert', [
                'etudiant_id' => $etudiant->id,
                'source' => $source,
            ]);

            return null;
        }

        $filename = $this->availableFilename(
            self::STUDENT_ROOT . "/{$toClassName}",
            $etudiant->photo
        );

        $target = self::STUDENT_ROOT . "/{$toClassName}/{$filename}";

        return $this->move($source, $target) ? $filename : null;
    }

    /**
     * Renomme les dossiers photos (eleves et candidats libres) suite au
     * changement de nom d'une classe. Les fichiers deja presents dans le
     * dossier de destination ne sont pas ecrases.
     */
    public function renameClassFolders(string $oldName, string $newName): void
    {
        if ($oldName === $newName) {
            return;
        }

        foreach ([self::STUDENT_ROOT, self::CANDIDATE_ROOT] as $root) {
            $from = "{$root}/{$oldName}";
            $to = "{$root}/{$newName}";

            if (! Storage::disk('public')->exists($from)) {
                continue;
            }

            foreach (Storage::disk('public')->files($from) as $file) {
                $filename = $this->availableFilename($to, basename($file));
                $this->move($file, "{$to}/{$filename}");
            }

            // Le dossier d'origine, desormais vide, est retire.
            if (Storage::disk('public')->files($from) === []) {
                Storage::disk('public')->deleteDirectory($from);
                $this->deleteMirroredDirectory($from);
            }

            Log::info('[photos] dossier de classe renomme', ['de' => $from, 'vers' => $to]);
        }
    }

    /**
     * Supprime la photo d'un eleve (disque + copie publique).
     */
    public function deleteStudentPhoto(Etudiant $etudiant, string $className): void
    {
        if (empty($etudiant->photo)) {
            return;
        }

        $path = self::STUDENT_ROOT . "/{$className}/{$etudiant->photo}";

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $mirror = public_path("storage/{$path}");
        if (is_file($mirror)) {
            @unlink($mirror);
        }
    }

    /* ------------------------------------------------------------------ */

    private function exists(string $relativePath): bool
    {
        return Storage::disk('public')->exists($relativePath)
            || is_file(public_path("storage/{$relativePath}"));
    }

    /**
     * Deplace un fichier sur le disque public puis repercute l'operation sur
     * la copie servie depuis public/storage.
     */
    private function move(string $from, string $to): bool
    {
        try {
            $directory = dirname($to);

            if (! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            if (Storage::disk('public')->exists($from)) {
                Storage::disk('public')->move($from, $to);
            } elseif (is_file(public_path("storage/{$from}"))) {
                // Le fichier n'existe que dans la copie publique : on le
                // reinjecte sur le disque avant de le deplacer.
                Storage::disk('public')->put($to, (string) file_get_contents(public_path("storage/{$from}")));
            } else {
                return false;
            }

            $this->mirror($to);
            $this->deleteMirroredFile($from);

            return true;
        } catch (\Throwable $e) {
            Log::error('[photos] echec du deplacement', [
                'de' => $from,
                'vers' => $to,
                'erreur' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Recopie un fichier du disque vers public/storage.
     */
    private function mirror(string $relativePath): void
    {
        $source = storage_path('app/public/' . $relativePath);
        $destination = public_path('storage/' . $relativePath);

        if (! is_file($source)) {
            return;
        }

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (@copy($source, $destination)) {
            @chmod($destination, 0644);
        }
    }

    private function deleteMirroredFile(string $relativePath): void
    {
        $mirror = public_path("storage/{$relativePath}");
        if (is_file($mirror)) {
            @unlink($mirror);
        }
    }

    private function deleteMirroredDirectory(string $relativePath): void
    {
        $directory = public_path("storage/{$relativePath}");
        if (is_dir($directory) && count((array) scandir($directory)) <= 2) {
            @rmdir($directory);
        }
    }

    /**
     * Retourne un nom de fichier libre dans le dossier cible : si le nom est
     * deja pris, un suffixe numerique est ajoute plutot que d'ecraser.
     */
    private function availableFilename(string $directory, string $filename): string
    {
        if (! $this->exists("{$directory}/{$filename}")) {
            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        for ($i = 2; $i <= 100; $i++) {
            $candidate = $extension !== '' ? "{$base}_{$i}.{$extension}" : "{$base}_{$i}";

            if (! $this->exists("{$directory}/{$candidate}")) {
                return $candidate;
            }
        }

        return $extension !== ''
            ? "{$base}_" . uniqid() . ".{$extension}"
            : "{$base}_" . uniqid();
    }
}
