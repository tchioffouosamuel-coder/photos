<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 | Entree du journal d'activite.
 |
 | Le journal est en ajout seul. Une piste d'audit que l'on peut modifier ou
 | effacer depuis l'application ne prouve rien : les deux operations sont donc
 | refusees au niveau du modele, y compris pour un administrateur.
 |
 | L'effacement reste possible par une commande d'exploitation dediee, pour la
 | seule purge de retention (voir journal:purger).
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_log';

    protected $fillable = [
        'user_id', 'user_nom', 'user_role',
        'etablissement_id',
        'action', 'sujet_type', 'sujet_id', 'sujet_libelle',
        'avant', 'apres', 'ip', 'user_agent',
    ];

    protected $casts = [
        'avant' => 'array',
        'apres' => 'array',
        'created_at' => 'datetime',
    ];

    /** Autorise la purge de retention a contourner le verrou. */
    public static bool $purgeAutorisee = false;

    protected static function booted(): void
    {
        /*
         | Le journal est cloisonne comme le reste des donnees : un
         | etablissement ne lit pas les traces d'un autre.
         |
         | La portee est globale et non un filtre a appeler, pour la meme
         | raison que sur les eleves : l'oubli ne couterait pas une gene
         | d'affichage mais la lecture, par un etablissement, de qui a fait
         | quoi chez le voisin — adresses IP comprises.
         |
         | A noter : une trace dont l'etablissement est NULL n'est lisible que
         | par les comptes a portee globale. C'est voulu (voir AuditLogger).
         */
        static::addGlobalScope(new \App\Models\Scopes\PorteeEtablissement());

        static::updating(function () {
            throw new \RuntimeException(
                'Le journal d\'activité est en ajout seul : une entrée ne peut pas être modifiée.'
            );
        });

        static::deleting(function () {
            if (! static::$purgeAutorisee) {
                throw new \RuntimeException(
                    'Le journal d\'activité est en ajout seul : une entrée ne peut pas être supprimée.'
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    /* ====================================================================
     | Filtres de consultation
     * ================================================================== */

    public function scopeDeLAuteur(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeSurSujet(Builder $query, string $type, $id): Builder
    {
        return $query->where('sujet_type', $type)->where('sujet_id', $id);
    }

    public function scopeEntre(Builder $query, ?string $debut, ?string $fin): Builder
    {
        if ($debut) {
            $query->whereDate('created_at', '>=', $debut);
        }

        if ($fin) {
            $query->whereDate('created_at', '<=', $fin);
        }

        return $query;
    }

    /* ====================================================================
     | Presentation
     * ================================================================== */

    /**
     * Auteur lisible, meme si le compte a ete supprime depuis.
     */
    public function getAuteurAttribute(): string
    {
        return $this->user_nom ?: 'Compte supprimé';
    }

    /**
     * Champs reellement modifies, pour l'affichage du detail.
     *
     * @return array<string, array{avant: mixed, apres: mixed}>
     */
    public function differences(): array
    {
        $avant = $this->avant ?? [];
        $apres = $this->apres ?? [];

        $champs = array_unique(array_merge(array_keys($avant), array_keys($apres)));
        $diff = [];

        foreach ($champs as $champ) {
            $a = $avant[$champ] ?? null;
            $b = $apres[$champ] ?? null;

            if ($a !== $b) {
                $diff[$champ] = ['avant' => $a, 'apres' => $b];
            }
        }

        return $diff;
    }
}
