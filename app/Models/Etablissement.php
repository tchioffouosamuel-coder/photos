<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/*
 | Un etablissement scolaire.
 |
 | C'est la racine de cloisonnement de l'application : une classe, un eleve,
 | un candidat libre et un compte appartiennent a un etablissement, et rien de
 | ce qui appartient a l'un n'est visible depuis l'autre.
 |
 | Les donnees portees ici (code, nom, adresse) remplacent config/school.php :
 | elles varient d'un etablissement a l'autre et ne peuvent donc plus vivre
 | dans le fichier .env du serveur.
 |
 | Le champ prefixe_photos merite une explication. Les photos sont rangees
 | dans storage/app/public/photos/{classe}/ : deux etablissements ayant tous
 | deux une « Tle A4 » melangeraient leurs fichiers. Le prefixe isole les
 | dossiers par etablissement.
 |
 | L'etablissement d'origine, celui cree par la migration a partir des
 | donnees existantes, a volontairement un prefixe NULL : ses photos restent
 | la ou elles sont deja sur le serveur, et le deploiement ne deplace aucun
 | fichier. Seuls les etablissements crees ensuite recoivent un prefixe.
 */
class Etablissement extends Model
{
    protected $table = 'etablissements';

    protected $fillable = [
        'code',
        'nom',
        'sigle',
        'ville',
        'region',
        'adresse',
        'telephone',
        'email',
        'prefixe_photos',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /* ====================================================================
     | Relations
     * ================================================================== */

    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class);
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class);
    }

    public function candidatsLibres(): HasMany
    {
        return $this->hasMany(CandidatLibre::class);
    }

    /**
     * Comptes rattaches a cet etablissement.
     *
     * Les comptes a portee globale (etablissement_id null) n'y figurent pas :
     * ils ne sont rattaches a aucun etablissement en particulier.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /* ====================================================================
     | Etat
     * ================================================================== */

    /**
     * Cet etablissement contient-il encore quelque chose ?
     *
     * Les requetes retirent la portee globale : on compte ce que
     * l'etablissement contient reellement, pas ce que le compte connecte a
     * le droit de voir. Sans cela, un etablissement plein paraitrait vide a
     * qui regarde ailleurs, et la suppression serait autorisee a tort.
     */
    public function aDesDonnees(): bool
    {
        foreach ([Classe::class, Etudiant::class, CandidatLibre::class] as $modele) {
            $existe = $modele::withoutGlobalScopes()
                ->where('etablissement_id', $this->getKey())
                ->exists();

            if ($existe) {
                return true;
            }
        }

        return User::where('etablissement_id', $this->getKey())->exists();
    }

    /* ====================================================================
     | Presentation
     * ================================================================== */

    public function getLibelleAttribute(): string
    {
        return $this->sigle ? "{$this->nom} ({$this->sigle})" : (string) $this->nom;
    }

    public function getInitialesAttribute(): string
    {
        if ($this->sigle) {
            return mb_strtoupper(mb_substr($this->sigle, 0, 2));
        }

        $mots = preg_split('/\s+/', trim((string) $this->nom)) ?: [];
        $mots = array_values(array_filter($mots, fn ($m) => $m !== ''));

        if (count($mots) >= 2) {
            return mb_strtoupper(mb_substr($mots[0], 0, 1) . mb_substr($mots[1], 0, 1));
        }

        return mb_strtoupper(mb_substr((string) $this->nom, 0, 2));
    }

    /* ====================================================================
     | Stockage des photos
     * ================================================================== */

    /**
     * Segment de dossier propre a cet etablissement, ou chaine vide pour
     * l'etablissement d'origine. Voir la note en tete de classe.
     */
    public function segmentPhotos(): string
    {
        return (string) ($this->prefixe_photos ?? '');
    }

    /**
     * Fabrique un prefixe de dossier sur depuis un code ou un nom.
     *
     * Le resultat ne contient que des lettres, des chiffres et des tirets :
     * il est utilise tel quel comme nom de dossier sur le serveur.
     */
    public static function prefixeDepuis(string $valeur): string
    {
        $prefixe = Str::slug($valeur, '-');

        /* Un code entierement non latin (ou vide) donnerait un slug vide. */
        if ($prefixe === '') {
            $prefixe = 'etab-' . Str::lower(Str::random(6));
        }

        return mb_substr($prefixe, 0, 40);
    }

    /**
     * Code etablissement imprime sur les documents (photos 4x4, cartes).
     */
    public function codeDocument(): string
    {
        return trim((string) ($this->code ?? ''));
    }
}
