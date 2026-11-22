<?php

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Controle d'acces par role et journal d'activite.
 |
 | Point de vigilance principal : l'application fonctionnait jusqu'ici sans
 | aucun role, tout compte connecte pouvant tout faire. Si cette migration
 | attribuait un role restreint aux comptes existants, l'administrateur
 | perdrait l'acces a sa propre application au moment du deploiement.
 |
 | Tous les comptes existants sont donc promus Administrateur : le
 | comportement reste exactement celui d'avant, et les droits se resserrent
 | ensuite depuis l'interface, compte par compte, en connaissance de cause.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->creerTables();
        $this->semerRolesEtPermissions();
        $this->promouvoirComptesExistants();
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                /* SQLite ne sait pas toujours supprimer une contrainte :
                   on tente, et on se contente de la colonne si cela echoue. */
                try {
                    $table->dropForeign(['role_id']);
                } catch (\Throwable $e) {
                    // contrainte absente ou non supprimable : sans consequence
                }

                $table->dropColumn('role_id');
            });
        }

        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('classe_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    /* ================================================================== */

    private function creerTables(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('nom');
                $table->string('description')->nullable();
                /* Un role systeme ne peut pas etre supprime depuis l'interface */
                $table->boolean('est_systeme')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('nom');
                $table->string('groupe')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('email')
                    ->constrained('roles')->nullOnDelete();
            });
        }

        /* Classes confiees a un operateur photo */
        if (! Schema::hasTable('classe_user')) {
            Schema::create('classe_user', function (Blueprint $table) {
                $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                /* Les deux horodatages : withTimestamps() les attend tous deux,
                   et la date d'assignation est une information d'audit utile. */
                $table->timestamps();
                $table->primary(['classe_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->id();

                /*
                 | L'auteur est conserve en clair en plus de la cle etrangere :
                 | une trace doit rester lisible apres la suppression du compte.
                 */
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_nom')->nullable();
                $table->string('user_role')->nullable();

                $table->string('action')->index();
                $table->string('sujet_type')->nullable();
                $table->unsignedBigInteger('sujet_id')->nullable();
                $table->string('sujet_libelle')->nullable();

                $table->json('avant')->nullable();
                $table->json('apres')->nullable();

                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();

                /* Pas de updated_at : une entree de journal ne se modifie pas. */
                $table->timestamp('created_at')->nullable()->index();

                $table->index(['sujet_type', 'sujet_id']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    private function semerRolesEtPermissions(): void
    {
        $maintenant = now();

        /* ---- Permissions ---- */
        $idParCode = [];

        foreach (Permissions::catalogue() as $code => $meta) {
            $existant = DB::table('permissions')->where('code', $code)->first();

            if ($existant) {
                DB::table('permissions')->where('id', $existant->id)->update([
                    'nom' => $meta['nom'],
                    'groupe' => $meta['groupe'],
                    'updated_at' => $maintenant,
                ]);
                $idParCode[$code] = $existant->id;
                continue;
            }

            $idParCode[$code] = DB::table('permissions')->insertGetId([
                'code' => $code,
                'nom' => $meta['nom'],
                'groupe' => $meta['groupe'],
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ]);
        }

        /* ---- Roles et dotation initiale ---- */
        foreach (Roles::catalogue() as $code => $meta) {
            $role = DB::table('roles')->where('code', $code)->first();

            if ($role) {
                $roleId = $role->id;
            } else {
                $roleId = DB::table('roles')->insertGetId([
                    'code' => $code,
                    'nom' => $meta['nom'],
                    'description' => $meta['description'],
                    'est_systeme' => true,
                    'created_at' => $maintenant,
                    'updated_at' => $maintenant,
                ]);
            }

            /*
             | La dotation n'est posee que si le role n'a encore aucune
             | permission : on ne veut pas ecraser un reglage fait depuis
             | l'interface si la migration est rejouee.
             */
            $deja = DB::table('permission_role')->where('role_id', $roleId)->exists();

            if ($deja) {
                continue;
            }

            $lignes = [];
            foreach ($meta['permissions'] as $permission) {
                if (isset($idParCode[$permission])) {
                    $lignes[] = ['role_id' => $roleId, 'permission_id' => $idParCode[$permission]];
                }
            }

            if ($lignes !== []) {
                DB::table('permission_role')->insert($lignes);
            }
        }
    }

    /**
     * Voir la note en tete de fichier : aucun compte ne doit perdre l'acces
     * au moment ou le controle d'acces entre en service.
     */
    private function promouvoirComptesExistants(): void
    {
        $administrateur = DB::table('roles')->where('code', Roles::ADMINISTRATEUR)->first();

        if (! $administrateur) {
            return;
        }

        DB::table('users')->whereNull('role_id')->update(['role_id' => $administrateur->id]);
    }
};
