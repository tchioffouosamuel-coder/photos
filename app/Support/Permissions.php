<?php

namespace App\Support;

/*
 | Catalogue des permissions de l'application.
 |
 | Les codes vivent ici, dans le code, pour trois raisons :
 |   - ils sont utilisables tels quels dans les vues et les routes
 |     (@can(Permissions::CLASSES_SUPPRIMER), ->middleware('can:...')),
 |   - une faute de frappe se voit a la relecture plutot qu'a l'execution,
 |   - la liste sert de source a la synchronisation de la table permissions.
 |
 | L'ATTRIBUTION des permissions aux roles, elle, vit en base : elle doit
 | pouvoir changer sans redeploiement.
 */
final class Permissions
{
    /* ---- Classes ---------------------------------------------------- */
    public const CLASSES_VOIR = 'classes.voir';
    public const CLASSES_CREER = 'classes.creer';
    public const CLASSES_MODIFIER = 'classes.modifier';
    public const CLASSES_SUPPRIMER = 'classes.supprimer';

    /* ---- Eleves ------------------------------------------------------ */
    public const ELEVES_VOIR = 'eleves.voir';
    public const ELEVES_CREER = 'eleves.creer';
    public const ELEVES_MODIFIER = 'eleves.modifier';
    public const ELEVES_SUPPRIMER = 'eleves.supprimer';
    public const ELEVES_TRANSFERER = 'eleves.transferer';
    public const ELEVES_IMPORTER = 'eleves.importer';

    /* ---- Photos ------------------------------------------------------ */
    public const PHOTOS_PRENDRE = 'photos.prendre';
    public const PHOTOS_IMPORTER = 'photos.importer';
    public const PHOTOS_SUPPRIMER = 'photos.supprimer';

    /* ---- Candidats libres -------------------------------------------- */
    public const CANDIDATS_VOIR = 'candidats.voir';
    public const CANDIDATS_CREER = 'candidats.creer';
    public const CANDIDATS_MODIFIER = 'candidats.modifier';
    public const CANDIDATS_SUPPRIMER = 'candidats.supprimer';

    /* ---- Production de documents ------------------------------------- */
    public const DOCUMENTS_GENERER = 'documents.generer';
    public const DOCUMENTS_GENERER_GLOBAL = 'documents.generer_global';

    /* ---- Etablissements ----------------------------------------------- */
    public const ETABLISSEMENTS_VOIR = 'etablissements.voir';
    public const ETABLISSEMENTS_GERER = 'etablissements.gerer';

    /* ---- Pilotage ----------------------------------------------------- */
    public const STATISTIQUES_VOIR = 'statistiques.voir';
    public const JOURNAL_CONSULTER = 'journal.consulter';
    public const COMPTES_GERER = 'comptes.gerer';

    /**
     * Catalogue complet : code => [libelle, groupe].
     *
     * @return array<string, array{nom: string, groupe: string}>
     */
    public static function catalogue(): array
    {
        return [
            self::CLASSES_VOIR => ['nom' => 'Consulter les classes', 'groupe' => 'Classes'],
            self::CLASSES_CREER => ['nom' => 'Créer une classe', 'groupe' => 'Classes'],
            self::CLASSES_MODIFIER => ['nom' => 'Modifier une classe', 'groupe' => 'Classes'],
            self::CLASSES_SUPPRIMER => ['nom' => 'Supprimer une classe', 'groupe' => 'Classes'],

            self::ELEVES_VOIR => ['nom' => 'Consulter les élèves', 'groupe' => 'Élèves'],
            self::ELEVES_CREER => ['nom' => 'Ajouter un élève', 'groupe' => 'Élèves'],
            self::ELEVES_MODIFIER => ['nom' => 'Modifier un élève', 'groupe' => 'Élèves'],
            self::ELEVES_SUPPRIMER => ['nom' => 'Supprimer un élève', 'groupe' => 'Élèves'],
            self::ELEVES_TRANSFERER => ['nom' => 'Transférer des élèves', 'groupe' => 'Élèves'],
            self::ELEVES_IMPORTER => ['nom' => 'Importer une liste d\'élèves', 'groupe' => 'Élèves'],

            self::PHOTOS_PRENDRE => ['nom' => 'Prendre et remplacer les photos', 'groupe' => 'Photos'],
            self::PHOTOS_IMPORTER => ['nom' => 'Importer des photos en lot', 'groupe' => 'Photos'],
            self::PHOTOS_SUPPRIMER => ['nom' => 'Supprimer une photo', 'groupe' => 'Photos'],

            self::CANDIDATS_VOIR => ['nom' => 'Consulter les candidats libres', 'groupe' => 'Candidats libres'],
            self::CANDIDATS_CREER => ['nom' => 'Ajouter un candidat libre', 'groupe' => 'Candidats libres'],
            self::CANDIDATS_MODIFIER => ['nom' => 'Modifier un candidat libre', 'groupe' => 'Candidats libres'],
            self::CANDIDATS_SUPPRIMER => ['nom' => 'Supprimer un candidat libre', 'groupe' => 'Candidats libres'],

            self::DOCUMENTS_GENERER => ['nom' => 'Générer les documents d\'une classe', 'groupe' => 'Production'],
            self::DOCUMENTS_GENERER_GLOBAL => ['nom' => 'Générer pour tout l\'établissement', 'groupe' => 'Production'],

            self::ETABLISSEMENTS_VOIR => ['nom' => 'Consulter les établissements', 'groupe' => 'Établissements'],
            self::ETABLISSEMENTS_GERER => ['nom' => 'Créer et modifier les établissements', 'groupe' => 'Établissements'],

            self::STATISTIQUES_VOIR => ['nom' => 'Consulter les statistiques', 'groupe' => 'Pilotage'],
            self::JOURNAL_CONSULTER => ['nom' => 'Consulter le journal d\'activité', 'groupe' => 'Pilotage'],
            self::COMPTES_GERER => ['nom' => 'Gérer les comptes et les rôles', 'groupe' => 'Pilotage'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::catalogue());
    }

    /**
     * Permissions groupees par rubrique, pour l'affichage d'un formulaire.
     *
     * @return array<string, array<string, string>> groupe => [code => nom]
     */
    public static function parGroupe(): array
    {
        $groupes = [];

        foreach (self::catalogue() as $code => $meta) {
            $groupes[$meta['groupe']][$code] = $meta['nom'];
        }

        return $groupes;
    }

    public static function libelle(string $code): string
    {
        return self::catalogue()[$code]['nom'] ?? $code;
    }
}
