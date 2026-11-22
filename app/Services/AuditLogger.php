<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/*
 | Ecriture du journal d'activite.
 |
 | Deux principes :
 |
 | 1. Une trace ne doit jamais faire echouer l'action qu'elle decrit. Toute
 |    erreur d'ecriture est capturee et renvoyee vers les logs applicatifs :
 |    mieux vaut une trace manquante signalee qu'un transfert d'eleves qui
 |    echoue parce que le journal est plein.
 |
 | 2. Aucun secret dans le journal. Les champs sensibles sont retires avant
 |    enregistrement, sans quoi un changement de mot de passe deposerait son
 |    empreinte dans une table consultable depuis l'interface.
 */
class AuditLogger
{
    /** Champs jamais enregistres, quelle que soit l'entite. */
    private const CHAMPS_EXCLUS = [
        'password', 'remember_token', 'api_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /** Champs sans interet pour une relecture d'audit. */
    private const CHAMPS_IGNORES = ['created_at', 'updated_at'];

    /* ---- Actions normalisees ---------------------------------------- */
    public const CREATION = 'creation';
    public const MODIFICATION = 'modification';
    public const SUPPRESSION = 'suppression';
    public const PHOTO_PRISE = 'photo.prise';
    public const PHOTO_REMPLACEE = 'photo.remplacee';
    public const PHOTO_SUPPRIMEE = 'photo.supprimee';
    public const PHOTOS_IMPORTEES = 'photos.importees';
    public const TRANSFERT = 'eleves.transfert';
    public const IMPORT_LISTE = 'import.liste';
    public const DOCUMENT_GENERE = 'document.genere';
    public const CONNEXION = 'auth.connexion';
    public const DECONNEXION = 'auth.deconnexion';
    public const CONNEXION_ECHOUEE = 'auth.echec';
    public const ROLE_MODIFIE = 'compte.role';

    /**
     * Libelles lisibles, pour l'interface de consultation.
     *
     * @return array<string, string>
     */
    public static function libelles(): array
    {
        return [
            self::CREATION => 'Création',
            self::MODIFICATION => 'Modification',
            self::SUPPRESSION => 'Suppression',
            self::PHOTO_PRISE => 'Photo prise',
            self::PHOTO_REMPLACEE => 'Photo remplacée',
            self::PHOTO_SUPPRIMEE => 'Photo supprimée',
            self::PHOTOS_IMPORTEES => 'Import de photos',
            self::TRANSFERT => 'Transfert d\'élèves',
            self::IMPORT_LISTE => 'Import de liste',
            self::DOCUMENT_GENERE => 'Document généré',
            self::CONNEXION => 'Connexion',
            self::DECONNEXION => 'Déconnexion',
            self::CONNEXION_ECHOUEE => 'Échec de connexion',
            self::ROLE_MODIFIE => 'Changement de rôle',
        ];
    }

    public static function libelleAction(string $action): string
    {
        return self::libelles()[$action] ?? $action;
    }

    /* ====================================================================
     | Ecriture
     * ================================================================== */

    /**
     * Enregistre une trace.
     *
     * @param array<string, mixed>|null $avant
     * @param array<string, mixed>|null $apres
     */
    public function enregistrer(
        string $action,
        ?Model $sujet = null,
        ?array $avant = null,
        ?array $apres = null,
        ?string $libelle = null
    ): ?ActivityLog {
        try {
            $utilisateur = Auth::user();
            $requete = request();

            return ActivityLog::create([
                'user_id' => $utilisateur?->getKey(),
                'user_nom' => $utilisateur?->name,
                'user_role' => $utilisateur?->role?->nom,

                'etablissement_id' => $this->etablissementDe($sujet, $utilisateur),

                'action' => $action,
                'sujet_type' => $sujet ? $sujet::class : null,
                'sujet_id' => $sujet?->getKey(),
                'sujet_libelle' => $libelle ?? ($sujet ? $this->libelleSujet($sujet) : null),

                'avant' => $this->nettoyer($avant),
                'apres' => $this->nettoyer($apres),

                'ip' => $requete?->ip(),
                'user_agent' => mb_substr((string) $requete?->userAgent(), 0, 255) ?: null,
            ]);
        } catch (\Throwable $e) {
            /* Voir le principe 1 en tete de classe. */
            Log::error('[audit] trace non enregistrée', [
                'action' => $action,
                'sujet' => $sujet ? $sujet::class . '#' . $sujet->getKey() : null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Trace la creation d'une entite.
     */
    public function creation(Model $sujet): ?ActivityLog
    {
        return $this->enregistrer(self::CREATION, $sujet, null, $this->attributs($sujet));
    }

    /**
     * Trace une modification, en ne gardant que les champs reellement changes.
     */
    public function modification(Model $sujet): ?ActivityLog
    {
        $modifies = $this->nettoyer($sujet->getDirty());

        if ($modifies === null || $modifies === []) {
            return null;   /* rien de significatif n'a change */
        }

        $avant = [];
        foreach (array_keys($modifies) as $champ) {
            $avant[$champ] = $sujet->getOriginal($champ);
        }

        return $this->enregistrer(self::MODIFICATION, $sujet, $avant, $modifies);
    }

    public function suppression(Model $sujet): ?ActivityLog
    {
        return $this->enregistrer(self::SUPPRESSION, $sujet, $this->attributs($sujet), null);
    }

    /* ====================================================================
     | Outils
     * ================================================================== */

    /**
     * Etablissement auquel rattacher une trace.
     *
     * Dans l'ordre : l'objet concerne, puis le compte qui agit, puis le
     * perimetre de la requete. Le premier est le plus fiable — modifier un
     * eleve concerne l'etablissement de cet eleve, meme si l'auteur est un
     * administrateur a portee globale qui n'en a aucun.
     *
     * Une trace sans etablissement (null) reste lisible par les comptes a
     * portee globale uniquement. C'est le cas des connexions de ces comptes,
     * et c'est le bon comportement : leur activite ne concerne aucun
     * etablissement en particulier.
     */
    private function etablissementDe(?Model $sujet, $utilisateur): ?int
    {
        if ($sujet !== null) {
            if ($sujet instanceof \App\Models\Etablissement) {
                return (int) $sujet->getKey();
            }

            $id = $sujet->getAttribute('etablissement_id');

            if ($id !== null) {
                return (int) $id;
            }
        }

        if ($utilisateur?->etablissement_id !== null) {
            return (int) $utilisateur->etablissement_id;
        }

        return app(\App\Support\EtablissementContext::class)->idActif();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributs(Model $sujet): array
    {
        return $this->nettoyer($sujet->getAttributes()) ?? [];
    }

    /**
     * Retire les secrets et le bruit.
     *
     * @param array<string, mixed>|null $valeurs
     * @return array<string, mixed>|null
     */
    private function nettoyer(?array $valeurs): ?array
    {
        if ($valeurs === null) {
            return null;
        }

        $propre = [];

        foreach ($valeurs as $champ => $valeur) {
            if (in_array($champ, self::CHAMPS_EXCLUS, true)) {
                continue;
            }

            if (in_array($champ, self::CHAMPS_IGNORES, true)) {
                continue;
            }

            /* Les objets et tableaux sont ramenes a une forme lisible. */
            if ($valeur instanceof \DateTimeInterface) {
                $valeur = $valeur->format('Y-m-d H:i:s');
            } elseif (is_object($valeur)) {
                $valeur = method_exists($valeur, '__toString') ? (string) $valeur : null;
            } elseif (is_array($valeur)) {
                $valeur = json_encode($valeur, JSON_UNESCAPED_UNICODE);
            }

            $propre[$champ] = $valeur;
        }

        return $propre;
    }

    /**
     * Libelle lisible d'une entite, pour retrouver une trace sans jointure.
     */
    private function libelleSujet(Model $sujet): ?string
    {
        foreach (['nom_complet', 'nom', 'name', 'libelle', 'titre'] as $champ) {
            $valeur = $sujet->getAttribute($champ);

            if (is_string($valeur) && $valeur !== '') {
                $prenoms = $sujet->getAttribute('prenoms');

                return $prenoms && $prenoms !== $valeur
                    ? trim($valeur . ' ' . $prenoms)
                    : $valeur;
            }
        }

        return class_basename($sujet) . ' #' . $sujet->getKey();
    }
}
