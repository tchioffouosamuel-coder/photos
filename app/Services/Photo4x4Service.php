<?php

namespace App\Services;

use App\Models\Classe;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/*
 | Planche de photos d'identite 4x4 prete a imprimer.
 |
 | Chaque bloc fait exactement 40 x 40 mm et contient, de haut en bas :
 |     - la date de production,
 |     - la photo de l'eleve,
 |     - le nom complet,
 |     - l'examen, le code du candidat et le code de l'etablissement.
 |
 | Points de vigilance, tous appris de la version precedente :
 |
 | 1. Le bloc est CARRE. L'image composee doit donc l'etre aussi, sinon FPDF
 |    l'etire pour remplir 40x40 mm et les visages sont deformes.
 |
 | 2. La photo est recadree (cover) a la taille EXACTE de sa zone. Un simple
 |    redimensionnement « au plus grand » la laissait deborder sur les bandes
 |    de texte : le bord de la photo traversait alors la date et le nom.
 |
 | 3. Les textes sont ajustes a la largeur disponible avant d'etre traces.
 |    Une troncature a l'aveugle coupait les noms longs en plein milieu.
 |
 | 4. La planche est composee a 300 ppp. A 96 ppp, le tirage etait flou.
 */
class Photo4x4Service
{
    /** Cote du bloc imprime, en millimetres. */
    private const BLOC_MM = 40.0;

    /** Espace entre deux blocs, en millimetres. */
    private const GOUTTIERE_MM = 5.0;

    /** Resolution de composition : 300 ppp donne un tirage net. */
    private const PPP = 300;

    /** Marges de la page A4, en millimetres. */
    private const MARGE_HAUT_MM = 10.0;
    private const MARGE_BAS_MM = 15.0;

    private const A4_LARGEUR_MM = 210.0;
    private const A4_HAUTEUR_MM = 297.0;

    private ImageManager $manager;

    /** Titre repris en tete de chaque page de la planche en cours. */
    private string $titre = '';

    /** Ordonnee de la ligne de blocs en cours, en millimetres. */
    private float $yCourant = 0.0;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Produit la planche et renvoie le chemin du PDF.
     */
    public function generatePrintablePhotos(Classe $classe): string
    {
        $etudiants = $classe->etudiants()
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->orderBy('nom')
            ->get();

        if ($etudiants->isEmpty()) {
            throw new \Exception('Aucun élève avec photo pour cette classe.');
        }

        $tempDir = storage_path(
            'app/temp/photos_4x4_' . $this->sanitizeFileName($classe->nom) . '_' . uniqid()
        );

        if (! is_dir($tempDir) && ! mkdir($tempDir, 0775, true) && ! is_dir($tempDir)) {
            throw new \Exception('Impossible de créer le dossier de travail.');
        }

        try {
            $date = date('d/m/Y');
            /*
             | Le code vient de l'etablissement de la classe, plus de
             | config/school.php : avec plusieurs etablissements, le code du
             | .env imprimerait celui d'un seul sur les documents de tous.
             */
            $codeEtablissement = $classe->codeEtablissement();

            $composees = [];
            $ignores = [];

            foreach ($etudiants as $etudiant) {
                try {
                    $composees[] = [
                        'path' => $this->composerBloc($etudiant, $tempDir, $date, $codeEtablissement),
                        'etudiant' => $etudiant,
                    ];
                } catch (\Throwable $e) {
                    $ignores[] = trim($etudiant->nom . ' ' . $etudiant->prenoms);
                    Log::error('[photos 4x4] bloc non composé', [
                        'etudiant_id' => $etudiant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($composees === []) {
                throw new \Exception('Aucune photo n\'a pu être traitée.');
            }

            if ($ignores !== []) {
                Log::warning('[photos 4x4] élèves absents de la planche', ['eleves' => $ignores]);
            }

            return $this->composerPdf($classe, $composees, $date);
        } finally {
            /* Le dossier de travail part meme si la composition echoue. */
            $this->deleteDirectory($tempDir);
        }
    }

    /* ====================================================================
     | Composition d'un bloc
     * ================================================================== */

    /**
     * Compose l'image carree d'un eleve et renvoie son chemin.
     */
    private function composerBloc($etudiant, string $tempDir, string $date, string $codeEtablissement): string
    {
        $source = storage_path(
            'app/public/photos/' . $etudiant->classe->segment_photos . '/' . $etudiant->photo
        );

        if (! is_file($source)) {
            throw new \Exception("Photo introuvable : {$source}");
        }

        /* Bloc carre : 40 mm a 300 ppp. */
        $cote = (int) round(self::BLOC_MM / 25.4 * self::PPP);

        /* Bandes reservees au texte, exprimees en fraction du cote. */
        $bandeHaut = (int) round($cote * 0.085);   // la date
        $bandeBas = (int) round($cote * 0.175);    // le nom puis l'examen
        $hauteurPhoto = $cote - $bandeHaut - $bandeBas;

        $bloc = $this->manager->create($cote, $cote)->fill('ffffff');

        /*
         | cover() recadre a la taille exacte de la zone : la photo ne peut
         | plus deborder sur les bandes de texte, quelle que soit sa forme.
         */
        $photo = $this->manager->read($source)->cover($cote, $hauteurPhoto);
        $bloc->place($photo, 'top-left', 0, $bandeHaut);

        $police = $this->cheminPolice();
        $largeurUtile = (int) round($cote * 0.94);

        /* ---- Date, au-dessus de la photo ---- */
        [$texteDate, $tailleDate] = $this->ajusterTexte(
            $date,
            $largeurUtile,
            (int) round($cote * 0.062),
            (int) round($cote * 0.040),
            $police
        );

        $bloc->text($texteDate, (int) round($cote / 2), (int) round($bandeHaut / 2),
            function ($font) use ($police, $tailleDate) {
                $font->filename($police);
                $font->size($tailleDate);
                $font->color('0033cc');
                $font->align('center');
                $font->valign('middle');
            });

        /* ---- Nom complet, sous la photo ---- */
        $nomComplet = $this->nomComplet($etudiant);

        [$texteNom, $tailleNom] = $this->ajusterTexte(
            $nomComplet,
            $largeurUtile,
            (int) round($cote * 0.060),
            (int) round($cote * 0.034),
            $police
        );

        $bloc->text($texteNom, (int) round($cote / 2), $bandeHaut + $hauteurPhoto + (int) round($bandeBas * 0.30),
            function ($font) use ($police, $tailleNom) {
                $font->filename($police);
                $font->size($tailleNom);
                $font->color('0033cc');
                $font->align('center');
                $font->valign('middle');
            });

        /* ---- Examen, code candidat, code etablissement ---- */
        $mention = $this->ligneExamen($etudiant, $codeEtablissement);

        [$texteMention, $tailleMention] = $this->ajusterTexte(
            $mention,
            $largeurUtile,
            (int) round($cote * 0.056),
            (int) round($cote * 0.032),
            $police
        );

        $bloc->text($texteMention, (int) round($cote / 2), $bandeHaut + $hauteurPhoto + (int) round($bandeBas * 0.72),
            function ($font) use ($police, $tailleMention) {
                $font->filename($police);
                $font->size($tailleMention);
                $font->color('0033cc');
                $font->align('center');
                $font->valign('middle');
            });

        $chemin = $tempDir . '/' . $this->sanitizeFileName(
            $etudiant->nom . '_' . $etudiant->prenoms . '_' . $etudiant->id
        ) . '.jpg';

        $bloc->toJpeg(92)->save($chemin);

        return $chemin;
    }

    /**
     * Nom affiche sous la photo : nom de famille puis prenoms.
     *
     * Les listes importees avant la correction de l'import peuvent contenir le
     * nom complet dans les deux champs ; on evite alors de l'ecrire deux fois.
     */
    private function nomComplet($etudiant): string
    {
        $nom = trim((string) $etudiant->nom);
        $prenoms = trim((string) $etudiant->prenoms);

        if ($prenoms === '' || mb_strtoupper($prenoms) === mb_strtoupper($nom)) {
            return mb_strtoupper($nom);
        }

        return mb_strtoupper($nom . ' ' . $prenoms);
    }

    /**
     * Ligne d'identification : examen, code candidat, code etablissement.
     */
    private function ligneExamen($etudiant, string $codeEtablissement): string
    {
        $morceaux = array_filter([
            mb_strtoupper(trim((string) ($etudiant->classe->exam ?? ''))),
            $this->getStudentCode($etudiant),
            trim($codeEtablissement),
        ], fn ($v) => $v !== '' && $v !== null);

        return implode(' ', $morceaux);
    }

    /**
     * Cherche la plus grande taille de police qui tient dans la largeur.
     *
     * Mesure reelle via imagettfbbox plutot qu'un comptage de caracteres :
     * « MOUKAM RUFIN » et « IIIIIIIIIIII » n'occupent pas la meme place.
     * En dernier recours seulement, le texte est abrege par des points de
     * suspension, jamais coupe net au milieu d'un mot.
     *
     * @return array{0: string, 1: int} [texte a tracer, taille de police]
     */
    private function ajusterTexte(string $texte, int $largeurMax, int $tailleMax, int $tailleMin, string $police): array
    {
        $texte = trim(preg_replace('/\s+/u', ' ', $texte));

        if ($texte === '') {
            return ['', $tailleMin];
        }

        $tailleMin = max(1, $tailleMin);
        $tailleMax = max($tailleMin, $tailleMax);

        for ($taille = $tailleMax; $taille >= $tailleMin; $taille--) {
            if ($this->largeurTexte($texte, $taille, $police) <= $largeurMax) {
                return [$texte, $taille];
            }
        }

        /* Trop long meme a la taille minimale : on abrege proprement. */
        $abrege = $texte;
        while (mb_strlen($abrege) > 1) {
            $abrege = rtrim(mb_substr($abrege, 0, mb_strlen($abrege) - 1));

            if ($this->largeurTexte($abrege . '.', $tailleMin, $police) <= $largeurMax) {
                return [$abrege . '.', $tailleMin];
            }
        }

        return [$texte, $tailleMin];
    }

    /**
     * Largeur en pixels d'un texte pour une police et une taille donnees.
     */
    private function largeurTexte(string $texte, int $taille, string $police): int
    {
        $boite = @imagettfbbox($taille, 0, $police, $texte);

        if ($boite === false) {
            /* Repli grossier si la mesure echoue : 0,55 em par caractere. */
            return (int) round(mb_strlen($texte) * $taille * 0.55);
        }

        return (int) abs($boite[2] - $boite[0]);
    }

    private function cheminPolice(): string
    {
        $police = public_path('fonts/arial.ttf');

        if (! is_file($police)) {
            throw new \Exception("Police introuvable : {$police}");
        }

        return $police;
    }

    /* ====================================================================
     | Mise en page de la planche
     * ================================================================== */

    private function composerPdf(Classe $classe, array $blocs, string $date): string
    {
        $this->titre = sprintf(
            'Photos 4x4 - %s - %d eleve%s - %s',
            $classe->nom,
            count($blocs),
            count($blocs) > 1 ? 's' : '',
            $date
        );
        $this->yCourant = 0.0;

        $pdf = new class extends Fpdf {
            public function Footer(): void
            {
                $this->SetY(-12);
                $this->SetFont('Arial', 'I', 8);
                $this->SetTextColor(120, 120, 120);
                $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
            }
        };

        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(false);

        /* Nombre de colonnes et centrage horizontal de la grille. */
        $colonnes = max(1, (int) floor(
            (self::A4_LARGEUR_MM - 2 * self::MARGE_HAUT_MM + self::GOUTTIERE_MM)
            / (self::BLOC_MM + self::GOUTTIERE_MM)
        ));

        $largeurGrille = $colonnes * self::BLOC_MM + ($colonnes - 1) * self::GOUTTIERE_MM;
        $xDepart = ($this->A4Largeur() - $largeurGrille) / 2;

        $entete = $this->hauteurEntete();
        $index = 0;
        $yLigne = 0.0;

        foreach ($blocs as $position => $bloc) {
            $colonne = $position % $colonnes;

            if ($colonne === 0) {
                $yLigne = $this->yProchaineLigne($pdf, $index, $entete);
                $index++;
            }

            $x = $xDepart + $colonne * (self::BLOC_MM + self::GOUTTIERE_MM);
            $y = $yLigne;

            try {
                /* L'image d'abord, le cadre ensuite : le trait reste visible. */
                $pdf->Image($bloc['path'], $x, $y, self::BLOC_MM, self::BLOC_MM);
                $pdf->SetDrawColor(170, 170, 170);
                $pdf->Rect($x, $y, self::BLOC_MM, self::BLOC_MM, 'D');
            } catch (\Throwable $e) {
                Log::error('[photos 4x4] bloc non place dans le PDF', [
                    'fichier' => $bloc['path'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $fichier = 'Photos_4x4_' . $this->sanitizeFileName($classe->nom) . '_' . date('Ymd_His') . '.pdf';
        $chemin = storage_path('app/public/generated/' . $fichier);

        if (! is_dir(dirname($chemin))) {
            mkdir(dirname($chemin), 0775, true);
        }

        $pdf->Output('F', $chemin);

        /*
         | Pas de copie vers public/storage : le controleur renvoie ce fichier
         | avec deleteFileAfterSend(), donc il disparait apres telechargement.
         | Une copie servie par URL resterait orpheline a chaque generation.
         */
        return $chemin;
    }

    /**
     * Ordonnee de la prochaine ligne, en ajoutant une page si besoin.
     *
     * Le controle est fait AVANT de tracer la ligne, et pour toute ligne y
     * compris la derniere, meme incomplete : l'ancienne version ne testait le
     * debordement qu'apres une ligne pleine, si bien qu'une ligne partielle
     * pouvait depasser le bas de la page.
     */
    private function yProchaineLigne(Fpdf $pdf, int $ligneIndex, float $entete): float
    {
        $hauteurUtile = self::A4_HAUTEUR_MM - self::MARGE_BAS_MM;
        $suivant = $this->yCourant + self::BLOC_MM + self::GOUTTIERE_MM;

        if ($ligneIndex === 0 || $suivant + self::BLOC_MM > $hauteurUtile) {
            $pdf->AddPage();
            $this->tracerEntete($pdf);
            $this->yCourant = self::MARGE_HAUT_MM + $entete;

            return $this->yCourant;
        }

        $this->yCourant = $suivant;

        return $this->yCourant;
    }

    private function hauteurEntete(): float
    {
        return 14.0;
    }

    /**
     * Titre repris sur chaque page : une planche imprimee se retrouve souvent
     * detachee de la premiere feuille.
     */
    private function tracerEntete(Fpdf $pdf): void
    {
        $pdf->SetY(self::MARGE_HAUT_MM);
        $pdf->SetTextColor(8, 43, 89);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 7, $this->latin($this->titre), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    private function A4Largeur(): float
    {
        return self::A4_LARGEUR_MM;
    }

    /**
     * FPDF n'accepte pas l'UTF-8 : les accents doivent etre convertis.
     */
    private function latin(string $texte): string
    {
        return mb_convert_encoding($texte, 'windows-1252', 'UTF-8');
    }

    /* ====================================================================
     | Utilitaires
     * ================================================================== */

    private function getStudentCode($etudiant): string
    {
        $lieu = (string) ($etudiant->lieu_naissance ?? '');

        if (str_contains($lieu, ',')) {
            $parties = explode(',', $lieu);

            return trim($parties[1]);
        }

        return 'CR';
    }

    private function sanitizeFileName(string $name): string
    {
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        return trim(preg_replace('/_+/', '_', $name), '_');
    }


    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (array_diff(scandir($dir), ['.', '..']) as $entree) {
            $chemin = $dir . '/' . $entree;
            is_dir($chemin) ? $this->deleteDirectory($chemin) : @unlink($chemin);
        }

        @rmdir($dir);
    }
}
