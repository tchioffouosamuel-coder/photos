<?php

namespace App\Support;

/*
 | Roles livres avec l'application et leur dotation initiale.
 |
 | Cette dotation n'est appliquee qu'a l'installation (ou lors d'une remise a
 | zero explicite) : une fois en service, les permissions d'un role se
 | modifient depuis l'interface, et la synchronisation ne doit pas ecraser ces
 | choix. Voir la commande rbac:sync.
 */
final class Roles
{
    public const ADMINISTRATEUR = 'administrateur';
    public const CHEF_ETABLISSEMENT = 'chef_etablissement';
    public const SECRETAIRE = 'secretaire';
    public const OPERATEUR_PHOTO = 'operateur_photo';
    public const LECTEUR = 'lecteur';

    /**
     * Roles dont l'acces est restreint aux classes qui leur sont assignees.
     *
     * @return list<string>
     */
    public static function portesParClasse(): array
    {
        return [self::OPERATEUR_PHOTO];
    }

    /**
     * @return array<string, array{nom: string, description: string, permissions: list<string>}>
     */
    public static function catalogue(): array
    {
        $p = Permissions::class;

        return [
            self::ADMINISTRATEUR => [
                'nom' => 'Administrateur',
                'description' => 'Accès complet, y compris la gestion des comptes et des rôles.',
                /* Dotation explicite malgre le contournement global : si le
                   contournement etait retire un jour, le role resterait juste. */
                'permissions' => Permissions::codes(),
            ],

            self::CHEF_ETABLISSEMENT => [
                'nom' => 'Chef d\'établissement',
                'description' => 'Pilote l\'établissement, produit les documents officiels et consulte le journal.',
                /*
                 | Tout, sauf deux choses qui ne relevent pas de la direction
                 | d'un etablissement : gerer les comptes, et creer ou
                 | supprimer des etablissements. Un chef d'etablissement lit
                 | la fiche du sien (ETABLISSEMENTS_VOIR, incluse ici), il
                 | n'administre pas le reseau.
                 */
                'permissions' => array_values(array_diff(
                    Permissions::codes(),
                    [$p::COMPTES_GERER, $p::ETABLISSEMENTS_GERER]
                )),
            ],

            self::SECRETAIRE => [
                'nom' => 'Secrétaire / Scolarité',
                'description' => 'Tient les classes et les effectifs. Ne supprime pas et ne produit pas les documents officiels.',
                'permissions' => [
                    $p::CLASSES_VOIR, $p::CLASSES_CREER, $p::CLASSES_MODIFIER,
                    $p::ELEVES_VOIR, $p::ELEVES_CREER, $p::ELEVES_MODIFIER,
                    $p::ELEVES_TRANSFERER, $p::ELEVES_IMPORTER,
                    $p::CANDIDATS_VOIR, $p::CANDIDATS_CREER, $p::CANDIDATS_MODIFIER,
                    $p::STATISTIQUES_VOIR,
                ],
            ],

            self::OPERATEUR_PHOTO => [
                'nom' => 'Opérateur photo',
                'description' => 'Prend les photos des classes qui lui sont assignées. Ne modifie pas l\'identité des élèves.',
                'permissions' => [
                    $p::CLASSES_VOIR,
                    $p::ELEVES_VOIR,
                    $p::PHOTOS_PRENDRE, $p::PHOTOS_IMPORTER,
                    $p::CANDIDATS_VOIR,
                    $p::STATISTIQUES_VOIR,
                ],
            ],

            self::LECTEUR => [
                'nom' => 'Lecteur',
                'description' => 'Consultation seule. Aucune écriture possible.',
                'permissions' => [
                    $p::CLASSES_VOIR,
                    $p::ELEVES_VOIR,
                    $p::CANDIDATS_VOIR,
                    $p::STATISTIQUES_VOIR,
                ],
            ],
        ];
    }

    public static function libelle(string $code): string
    {
        return self::catalogue()[$code]['nom'] ?? $code;
    }
}
