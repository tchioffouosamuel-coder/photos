<?php

use App\Models\Etablissement;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/*
 | Passage au multi-etablissement.
 |
 | L'application ne connaissait qu'un etablissement, decrit dans config/school.php
 | donc dans le .env du serveur. Cette migration en fait une entite de la base,
 | et rattache a elle tout ce qui existe deja.
 |
 | Trois precautions, dans l'ordre de ce qui ferait le plus de degats :
 |
 | 1. AUCUN FICHIER N'EST DEPLACE. Les photos vivent dans
 |    storage/app/public/photos/{classe}/ ; isoler les dossiers par
 |    etablissement imposerait de deplacer tout l'existant pendant un
 |    deploiement, sur un hebergement partage, sans transaction possible sur
 |    le systeme de fichiers. L'etablissement d'origine garde donc un
 |    prefixe_photos NULL : ses chemins restent exactement les memes. Seuls
 |    les etablissements crees ensuite recoivent un prefixe de dossier.
 |
 | 2. PERSONNE NE PERD L'ACCES. Les comptes administrateurs passent en portee
 |    globale (etablissement_id NULL) : sans cela, plus personne ne pourrait
 |    creer le second etablissement. Les autres comptes sont rattaches a
 |    l'etablissement d'origine, ou ils retrouvent exactement ce qu'ils
 |    voyaient avant.
 |
 | 3. LES COLONNES RESTENT NULLABLE. Un etablissement_id NULL sur un eleve le
 |    rend invisible aux comptes rattaches et visible aux seuls comptes a
 |    portee globale : l'oubli echoue en masquant, jamais en divulguant. Le
 |    rattachement est garanti par le code (voir AppartientAEtablissement),
 |    pas par une contrainte qu'un hebergement partage pourrait refuser de
 |    poser au mauvais moment.
 */
return new class extends Migration
{
    /** Tables recevant une colonne etablissement_id denormalisee. */
    private const TABLES_PORTEES = [
        'classes' => 'annee_scolaire',
        'etudiants' => 'classe_id',
        'candidats_libres' => 'classe_id',
    ];

    public function up(): void
    {
        $this->creerTableEtablissements();
        $this->ajouterColonnes();

        $etablissement = $this->creerEtablissementInitial();

        $this->rattacherDonneesExistantes($etablissement);
        $this->rattacherComptesExistants($etablissement);

        $this->ajusterUniciteDesClasses();
        $this->semerPermissions();
    }

    public function down(): void
    {
        foreach (['activity_log', 'import_history', 'users'] as $table) {
            $this->retirerColonne($table);
        }

        foreach (array_keys(self::TABLES_PORTEES) as $table) {
            $this->retirerColonne($table);
        }

        /* L'unicite d'origine portait sur le seul nom de classe. */
        if (Schema::hasTable('classes')) {
            $this->supprimerIndex('classes', ['etablissement_id', 'annee_scolaire', 'nom']);

            try {
                Schema::table('classes', function (Blueprint $table) {
                    $table->unique('nom');
                });
            } catch (\Throwable $e) {
                Log::warning('[etablissements] unicite d\'origine non restauree', ['erreur' => $e->getMessage()]);
            }
        }

        Schema::dropIfExists('etablissements');
    }

    /* ==================================================================
     | Schema
     * ================================================================ */

    private function creerTableEtablissements(): void
    {
        if (Schema::hasTable('etablissements')) {
            return;
        }

        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();

            /* Le code imprime sur les documents officiels (photos 4x4). */
            $table->string('code', 50)->unique();
            $table->string('nom');
            $table->string('sigle', 30)->nullable();

            $table->string('ville')->nullable();
            $table->string('region')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('email')->nullable();

            /*
             | Segment de dossier isolant les photos de cet etablissement.
             | NULL pour l'etablissement d'origine : voir la precaution 1.
             */
            $table->string('prefixe_photos', 40)->nullable()->unique();

            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    private function ajouterColonnes(): void
    {
        foreach (self::TABLES_PORTEES as $table => $apres) {
            $this->ajouterColonne($table, $apres);
        }

        /* Portee du compte : NULL signifie « tous les etablissements ». */
        $this->ajouterColonne('users', 'role_id');

        $this->ajouterColonne('import_history', 'classe_id');

        /* Le journal est cloisonne comme le reste : un etablissement ne lit
           pas les traces d'un autre. */
        $this->ajouterColonne('activity_log', 'user_role', nullOnDelete: true);
    }

    private function ajouterColonne(string $table, string $apres, bool $nullOnDelete = false): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'etablissement_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $colonnes) use ($table, $apres, $nullOnDelete) {
            $colonne = $colonnes->foreignId('etablissement_id')->nullable();

            if (Schema::hasColumn($table, $apres)) {
                $colonne->after($apres);
            }

            $colonne->index();
        });

        /*
         | La cle etrangere est posee a part, et son echec n'est pas fatal :
         | une colonne indexee sans contrainte reste exploitable, alors qu'une
         | migration interrompue au milieu laisse le schema a moitie pose.
         */
        try {
            Schema::table($table, function (Blueprint $colonnes) use ($nullOnDelete) {
                $contrainte = $colonnes->foreign('etablissement_id')->references('id')->on('etablissements');

                /*
                 | restrictOnDelete pour les donnees vivantes : supprimer un
                 | etablissement doit echouer bruyamment tant qu'il contient
                 | des classes, pas emporter ses eleves en silence.
                 | nullOnDelete pour le journal : une trace survit a tout.
                 */
                $nullOnDelete ? $contrainte->nullOnDelete() : $contrainte->restrictOnDelete();
            });
        } catch (\Throwable $e) {
            Log::warning('[etablissements] cle etrangere non posee', [
                'table' => $table,
                'erreur' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Le nom de classe etait unique pour toute la base.
     *
     * Deux defauts d'un coup : deux etablissements ne pouvaient pas avoir
     * tous deux une « Tle A4 », et un meme etablissement ne pouvait pas
     * reconduire une classe d'une annee scolaire sur la suivante. L'unicite
     * devient donc (etablissement, annee, nom).
     */
    private function ajusterUniciteDesClasses(): void
    {
        if (! Schema::hasTable('classes')) {
            return;
        }

        $this->supprimerIndex('classes', ['nom']);

        try {
            Schema::table('classes', function (Blueprint $table) {
                $table->unique(['etablissement_id', 'annee_scolaire', 'nom'], 'classes_etab_annee_nom_unique');
            });
        } catch (\Throwable $e) {
            Log::warning('[etablissements] unicite composite non posee', ['erreur' => $e->getMessage()]);
        }
    }

    /* ==================================================================
     | Donnees
     * ================================================================ */

    /**
     * Cree l'etablissement d'origine a partir de config/school.php.
     *
     * Les valeurs du .env du serveur en service decrivent precisement
     * l'etablissement qui l'utilise : elles deviennent sa fiche, et restent
     * modifiables depuis l'interface ensuite.
     */
    private function creerEtablissementInitial(): object
    {
        $existant = DB::table('etablissements')->orderBy('id')->first();

        if ($existant) {
            return $existant;
        }

        $maintenant = now();

        $code = trim((string) config('school.code', '')) ?: 'ETAB-001';
        $nom = trim((string) config('school.name', '')) ?: 'Établissement principal';

        $id = DB::table('etablissements')->insertGetId([
            'code' => mb_substr($code, 0, 50),
            'nom' => $nom,
            'sigle' => null,
            'adresse' => trim((string) config('school.address', '')) ?: null,
            'telephone' => trim((string) config('school.phone', '')) ?: null,
            'email' => trim((string) config('school.email', '')) ?: null,

            /* NULL : les photos existantes ne bougent pas. Precaution 1. */
            'prefixe_photos' => null,

            'actif' => true,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);

        Log::info('[etablissements] etablissement d\'origine cree', ['id' => $id, 'nom' => $nom]);

        return DB::table('etablissements')->where('id', $id)->first();
    }

    private function rattacherDonneesExistantes(object $etablissement): void
    {
        foreach (array_keys(self::TABLES_PORTEES) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'etablissement_id')) {
                continue;
            }

            DB::table($table)->whereNull('etablissement_id')
                ->update(['etablissement_id' => $etablissement->id]);
        }

        foreach (['import_history', 'activity_log'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'etablissement_id')) {
                DB::table($table)->whereNull('etablissement_id')
                    ->update(['etablissement_id' => $etablissement->id]);
            }
        }
    }

    /**
     * Voir la precaution 2 en tete de fichier.
     */
    private function rattacherComptesExistants(object $etablissement): void
    {
        if (! Schema::hasColumn('users', 'etablissement_id')) {
            return;
        }

        $idAdministrateur = DB::table('roles')->where('code', Roles::ADMINISTRATEUR)->value('id');

        /* Tout le monde est rattache a l'etablissement d'origine... */
        DB::table('users')->whereNull('etablissement_id')
            ->update(['etablissement_id' => $etablissement->id]);

        /* ...sauf les administrateurs, qui gardent la main sur l'ensemble. */
        if ($idAdministrateur) {
            DB::table('users')->where('role_id', $idAdministrateur)
                ->update(['etablissement_id' => null]);
        }
    }

    /**
     * Ajoute les deux permissions d'etablissement au catalogue en base, et
     * les attribue a l'administrateur.
     *
     * etablissements.voir est aussi donnee au chef d'etablissement : il doit
     * pouvoir lire la fiche de son etablissement. etablissements.gerer, non :
     * creer ou supprimer un etablissement n'est pas un acte de direction
     * d'ecole, c'est un acte d'administration du reseau.
     */
    private function semerPermissions(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $maintenant = now();
        $catalogue = Permissions::catalogue();

        $idParCode = [];

        foreach ([Permissions::ETABLISSEMENTS_VOIR, Permissions::ETABLISSEMENTS_GERER] as $code) {
            $existant = DB::table('permissions')->where('code', $code)->first();

            if ($existant) {
                $idParCode[$code] = $existant->id;
                continue;
            }

            $idParCode[$code] = DB::table('permissions')->insertGetId([
                'code' => $code,
                'nom' => $catalogue[$code]['nom'],
                'groupe' => $catalogue[$code]['groupe'],
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ]);
        }

        $dotation = [
            Roles::ADMINISTRATEUR => [Permissions::ETABLISSEMENTS_VOIR, Permissions::ETABLISSEMENTS_GERER],
            Roles::CHEF_ETABLISSEMENT => [Permissions::ETABLISSEMENTS_VOIR],
        ];

        foreach ($dotation as $codeRole => $codes) {
            $roleId = DB::table('roles')->where('code', $codeRole)->value('id');

            if (! $roleId) {
                continue;
            }

            foreach ($codes as $code) {
                $deja = DB::table('permission_role')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $idParCode[$code])
                    ->exists();

                if (! $deja) {
                    DB::table('permission_role')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $idParCode[$code],
                    ]);
                }
            }
        }
    }

    /* ==================================================================
     | Outils
     * ================================================================ */

    private function retirerColonne(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'etablissement_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $colonnes) {
            try {
                $colonnes->dropForeign(['etablissement_id']);
            } catch (\Throwable $e) {
                // contrainte absente : sans consequence
            }

            $colonnes->dropColumn('etablissement_id');
        });
    }

    /**
     * Supprime un index dont on ne connait pas le nom avec certitude : il
     * depend du moteur et de l'historique de la base.
     *
     * @param  list<string>  $colonnes
     */
    private function supprimerIndex(string $table, array $colonnes): void
    {
        try {
            foreach (Schema::getIndexes($table) as $index) {
                $memesColonnes = array_values((array) ($index['columns'] ?? [])) === $colonnes;

                if ($memesColonnes && ($index['unique'] ?? false) && ! ($index['primary'] ?? false)) {
                    Schema::table($table, function (Blueprint $t) use ($index) {
                        $t->dropIndex($index['name']);
                    });

                    Log::info('[etablissements] index supprime', ['nom' => $index['name']]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[etablissements] index non supprime', [
                'table' => $table,
                'colonnes' => $colonnes,
                'erreur' => $e->getMessage(),
            ]);
        }
    }
};
