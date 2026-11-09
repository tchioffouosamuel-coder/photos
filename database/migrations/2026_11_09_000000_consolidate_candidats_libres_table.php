<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/*
 | Consolidation du nom de la table des candidats libres.
 |
 | Historique du probleme : le modele App\Models\CandidatLibre ne declarait pas
 | de $table. Eloquent deduisait donc "candidat_libres" du nom de classe, alors
 | que la migration de creation produit "candidats_libres". Selon les
 | environnements, les donnees ont pu atterrir dans l'une ou l'autre table.
 |
 | Cette migration fait converger les deux cas vers "candidats_libres", qui est
 | desormais declaree explicitement dans le modele, sans jamais perdre de ligne :
 |
 |   - seule "candidat_libres" existe        -> renommee en "candidats_libres"
 |   - les deux existent                     -> les lignes de l'ancienne sont
 |                                              recopiees dans la nouvelle, puis
 |                                              l'ancienne est conservee sous le
 |                                              nom "candidat_libres_backup"
 |   - seule "candidats_libres" existe       -> rien a faire
 */
return new class extends Migration
{
    private const LEGACY = 'candidat_libres';
    private const TARGET = 'candidats_libres';
    private const BACKUP = 'candidat_libres_backup';

    public function up(): void
    {
        $legacyExists = Schema::hasTable(self::LEGACY);
        $targetExists = Schema::hasTable(self::TARGET);

        if (! $legacyExists) {
            // Rien a consolider : installation saine ou deja migree.
            return;
        }

        if (! $targetExists) {
            Schema::rename(self::LEGACY, self::TARGET);
            Log::info('[candidats_libres] table renommee', [
                'de' => self::LEGACY,
                'vers' => self::TARGET,
            ]);

            return;
        }

        // Les deux tables existent : on rapatrie les lignes de l'ancienne.
        $moved = $this->copyLegacyRows();

        if (Schema::hasTable(self::BACKUP)) {
            Schema::drop(self::BACKUP);
        }
        Schema::rename(self::LEGACY, self::BACKUP);

        Log::info('[candidats_libres] lignes rapatriees puis ancienne table sauvegardee', [
            'lignes_copiees' => $moved,
            'sauvegarde' => self::BACKUP,
        ]);
    }

    public function down(): void
    {
        // La sauvegarde est restauree si elle existe encore.
        if (Schema::hasTable(self::BACKUP) && ! Schema::hasTable(self::LEGACY)) {
            Schema::rename(self::BACKUP, self::LEGACY);
        }
    }

    /**
     * Recopie les lignes de l'ancienne table vers la nouvelle, en ignorant
     * celles qui y figurent deja (meme classe, meme nom, meme date de naissance).
     */
    private function copyLegacyRows(): int
    {
        $shared = array_values(array_intersect(
            Schema::getColumnListing(self::LEGACY),
            Schema::getColumnListing(self::TARGET)
        ));

        // L'identifiant n'est pas repris : il est regenere par la table cible.
        $shared = array_values(array_diff($shared, ['id']));

        if ($shared === []) {
            return 0;
        }

        $moved = 0;

        DB::table(self::LEGACY)->orderBy('id')->chunk(200, function ($rows) use ($shared, &$moved) {
            foreach ($rows as $row) {
                $values = [];
                foreach ($shared as $column) {
                    $values[$column] = $row->{$column} ?? null;
                }

                $duplicate = DB::table(self::TARGET)
                    ->where('classe_id', $values['classe_id'] ?? null)
                    ->where('nom', $values['nom'] ?? null)
                    ->where('date_naissance', $values['date_naissance'] ?? null)
                    ->exists();

                if ($duplicate) {
                    continue;
                }

                DB::table(self::TARGET)->insert($values);
                $moved++;
            }
        });

        return $moved;
    }
};
