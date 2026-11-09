<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
 | Fabrique le modele de liste d'eleves telecharge depuis la page d'import.
 |
 | Le modele est GENERE a la demande, et non lu depuis public/templates/ :
 | ce fichier binaire n'etait present sur aucun environnement, ce qui faisait
 | echouer la route de telechargement. Le generer supprime la dependance au
 | deploiement, et garantit surtout que le modele reste synchronise avec ce
 | que ExcelImportService sait relire.
 |
 | Disposition imposee par le lecteur (voir ExcelImportService) :
 |     A = N°   (chaine exacte recherchee pour trouver la ligne d'en-tete)
 |     B = matricule national
 |     C = matricule interne
 |     D = nom et prenoms
 |     E = date de naissance (JJ/MM/AAAA)
 |     F = lieu de naissance
 |     G = sexe   (M / F)
 |     H = statut (N / R)
 |
 | Les exemples sont volontairement places sur un second onglet : le lecteur
 | ne traite que le premier, donc un utilisateur qui oublie de les supprimer
 | n'importe pas de faux eleves.
 */
class ExcelTemplateService
{
    /** Couleurs de l'identite ACID, sans le croisillon. */
    private const NAVY = 'FF082B59';
    private const TEAL = 'FF18C5B2';
    private const CREAM = 'FFFFF9EF';
    private const BORDER = 'FFCBD6E4';

    /** Ligne de l'en-tete des colonnes dans l'onglet de saisie. */
    private const HEADER_ROW = 4;

    /** Nombre de lignes de saisie pre-formatees. */
    private const BLANK_ROWS = 60;

    /**
     * @var array<string, string> Colonne => intitule
     */
    private const COLUMNS = [
        'A' => 'N°',
        'B' => 'Matricule national',
        'C' => 'Matricule interne',
        'D' => 'Nom et prenoms',
        'E' => 'Date de naissance',
        'F' => 'Lieu de naissance',
        'G' => 'Sexe',
        'H' => 'Statut',
    ];

    /**
     * @var array<string, float> Colonne => largeur
     */
    private const WIDTHS = [
        'A' => 6,
        'B' => 20,
        'C' => 18,
        'D' => 34,
        'E' => 18,
        'F' => 22,
        'G' => 8,
        'H' => 9,
    ];

    /**
     * Construit le classeur et renvoie son contenu binaire.
     */
    public function build(): string
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator(config('school.name', 'ACID'))
            ->setTitle('Modele de liste d\'eleves')
            ->setDescription('Modele d\'import genere automatiquement par ACID.');

        $this->buildDataSheet($spreadsheet);
        $this->buildHelpSheet($spreadsheet);

        /* L'onglet de saisie doit etre celui que le lecteur trouvera actif */
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        /*
         | php://output n'est pas utilisable ici : on veut rendre le binaire a
         | l'appelant, qui decide de l'envoyer ou de l'ecrire. On passe donc par
         | un flux memoire.
         */
        $flux = fopen('php://memory', 'r+');
        $writer->save($flux);
        rewind($flux);
        $contenu = stream_get_contents($flux);
        fclose($flux);

        return $contenu;
    }

    /**
     * Onglet de saisie : titre, en-tete, lignes vides pretes a remplir.
     */
    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Eleves');

        /* ---- Bandeau de titre ---- */
        $sheet->setCellValue('A1', 'LISTE DES ELEVES');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        /* ---- Rappel du mode d'emploi, en clair ---- */
        $sheet->setCellValue(
            'A2',
            'Saisissez un eleve par ligne sous l\'en-tete. Ne modifiez ni l\'ordre '
            . 'des colonnes ni le libelle « N° » en A' . self::HEADER_ROW . '.'
        );
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF5A6B7D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::CREAM]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        /* ---- En-tete des colonnes ---- */
        foreach (self::COLUMNS as $col => $libelle) {
            $sheet->setCellValue($col . self::HEADER_ROW, $libelle);
            $sheet->getColumnDimension($col)->setWidth(self::WIDTHS[$col]);
        }

        $plageEntete = 'A' . self::HEADER_ROW . ':H' . self::HEADER_ROW;
        $sheet->getStyle($plageEntete)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::NAVY]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::TEAL]],
            ],
        ]);
        $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(30);

        /* ---- Zone de saisie ---- */
        $premiere = self::HEADER_ROW + 1;
        $derniere = self::HEADER_ROW + self::BLANK_ROWS;

        $sheet->getStyle("A{$premiere}:H{$derniere}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::BORDER]],
            ],
        ]);

        /* Numerotation pre-remplie : la colonne A n'est pas lue, elle ne sert
           qu'au reperage visuel. */
        for ($ligne = $premiere; $ligne <= $derniere; $ligne++) {
            $sheet->setCellValue('A' . $ligne, $ligne - self::HEADER_ROW);
        }

        $sheet->getStyle("A{$premiere}:A{$derniere}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$premiere}:H{$derniere}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        /*
         | Les matricules et la date restent du texte : sans cela Excel
         | transforme « 015 » en 15 et reinterprete les dates selon la langue
         | du poste, ce que le lecteur ne pourrait plus deviner.
         */
        foreach (['B', 'C', 'E'] as $col) {
            $sheet->getStyle("{$col}{$premiere}:{$col}{$derniere}")
                ->getNumberFormat()->setFormatCode('@');
        }

        /* ---- Listes de choix : evite les fautes de saisie ---- */
        $this->applyListValidation(
            $sheet,
            "G{$premiere}:G{$derniere}",
            '"M,F"',
            'Sexe',
            'Choisissez M (masculin) ou F (feminin).'
        );

        $this->applyListValidation(
            $sheet,
            "H{$premiere}:H{$derniere}",
            '"N,R"',
            'Statut',
            'N = Normal, R = Redoublant. Vide equivaut a N.'
        );

        /* L'en-tete reste visible pendant le defilement */
        $sheet->freezePane('A' . $premiere);
        $sheet->setSelectedCell('B' . $premiere);
    }

    /**
     * Onglet d'aide : exemples et consignes, hors de la zone lue a l'import.
     */
    private function buildHelpSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Exemples et consignes');

        $sheet->setCellValue('A1', 'EXEMPLES DE SAISIE');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->setCellValue(
            'A2',
            'Ces lignes sont des exemples : recopiez-les dans l\'onglet « Eleves », ne les importez pas telles quelles.'
        );
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        /* En-tete identique a celui de l'onglet de saisie, pour le repere */
        foreach (self::COLUMNS as $col => $libelle) {
            $sheet->setCellValue($col . '4', $libelle);
            $sheet->getColumnDimension($col)->setWidth(self::WIDTHS[$col]);
        }

        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF14508F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        /*
         | Les exemples illustrent la convention de nommage attendue :
         | le nom de famille en MAJUSCULES, les prenoms en casse normale.
         | C'est ce qui permet a l'import de les separer correctement, y
         | compris pour un nom compose.
         */
        $exemples = [
            ['1', '20080412MN01', 'INT-001', 'MBALLA Paul Eric', '12/04/2008', 'Yaounde', 'M', 'N'],
            ['2', '20070903MN02', 'INT-002', 'NGO BALEPA Amelie Therese', '03/09/2007', 'Douala', 'F', 'R'],
            ['3', '', 'INT-003', 'TCHOFFO Jean', '25/12/2008', 'Bafoussam', 'M', ''],
        ];

        $ligne = 5;
        foreach ($exemples as $exemple) {
            $col = 'A';
            foreach ($exemple as $valeur) {
                $sheet->setCellValueExplicit(
                    $col . $ligne,
                    $valeur,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $col++;
            }
            $ligne++;
        }

        $sheet->getStyle('A5:H' . ($ligne - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::BORDER]],
            ],
        ]);

        /* ---- Consignes ---- */
        $consignes = [
            'Regles de remplissage',
            'Colonne A (N°) : simple numerotation, elle n\'est pas importee.',
            'Colonne D : nom de famille en MAJUSCULES, puis prenoms en casse normale.',
            '    Exemple : « MBALLA Paul » donne le nom MBALLA et le prenom Paul.',
            '    Un nom compose fonctionne : « NGO BALEPA Amelie » donne NGO BALEPA / Amelie.',
            'Colonne E : date au format JJ/MM/AAAA, par exemple 15/08/2008.',
            'Colonne G : M ou F uniquement.',
            'Colonne H : N (normal) ou R (redoublant). Laisser vide equivaut a N.',
            '',
            'Mise a jour et doublons',
            'Un eleve dont le matricule national existe deja dans la classe est mis a jour,',
            'et non duplique. Sans matricule national, le rapprochement se fait sur le nom,',
            'les prenoms et la date de naissance.',
            '',
            'Points de vigilance',
            'Ne modifiez pas le libelle « N° » de la cellule A' . self::HEADER_ROW
                . ' de l\'onglet « Eleves » : c\'est lui qui',
            'indique au logiciel ou commencent les donnees.',
            'Ne changez pas l\'ordre des colonnes.',
            'Une ligne sans nom est ignoree.',
        ];

        $ligne += 2;

        foreach ($consignes as $texte) {
            $sheet->setCellValue('A' . $ligne, $texte);
            $sheet->mergeCells('A' . $ligne . ':H' . $ligne);

            $estTitre = in_array($texte, [
                'Regles de remplissage',
                'Mise a jour et doublons',
                'Points de vigilance',
            ], true);

            if ($estTitre) {
                $sheet->getStyle('A' . $ligne)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => self::NAVY]],
                ]);
            }

            $ligne++;
        }

        $sheet->getStyle('A1:H' . $ligne)->getAlignment()->setIndent(1);
    }

    /**
     * Applique une liste de choix a une plage de cellules.
     */
    private function applyListValidation(
        $sheet,
        string $plage,
        string $formule,
        string $titre,
        string $message
    ): void {
        $validation = $sheet->getCell(explode(':', $plage)[0])->getDataValidation();

        $validation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Valeur refusee')
            ->setError($message)
            ->setPromptTitle($titre)
            ->setPrompt($message)
            ->setFormula1($formule);

        $sheet->setDataValidation($plage, $validation);
    }
}
