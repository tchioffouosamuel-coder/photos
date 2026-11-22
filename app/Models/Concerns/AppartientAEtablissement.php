<?php

namespace App\Models\Concerns;

use App\Models\Etablissement;
use App\Models\Scopes\PorteeEtablissement;
use App\Support\EtablissementContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 | Rattache un modele a un etablissement, et le cloisonne.
 |
 | Le trait fait deux choses d'un coup, et c'est voulu : impossible de
 | rattacher une entite a un etablissement sans l'isoler du meme geste, ou
 | l'inverse. Les deux oublis se compensent mal.
 |
 | Le rattachement est DERIVE, jamais recopie depuis la requete HTTP. Un
 | eleve tient son etablissement de sa classe, une classe du contexte de la
 | requete. Si etablissement_id arrivait du formulaire, un champ cache suffit
 | a deposer un eleve chez le voisin.
 |
 | @mixin \Illuminate\Database\Eloquent\Model
 */
trait AppartientAEtablissement
{
    public static function bootAppartientAEtablissement(): void
    {
        static::addGlobalScope(new PorteeEtablissement());

        static::saving(function ($modele) {
            /*
             | Resoudre a chaque enregistrement couterait une requete par
             | ligne : un import de 300 eleves en paierait 300 pour rien. On
             | ne resout donc que si le rattachement manque, ou si ce dont il
             | depend vient de changer.
             */
            if (! $modele->etablissementDoitEtreResolu()) {
                return;
            }

            $resolu = $modele->resoudreEtablissementId();

            /*
             | Le rattachement derive l'emporte sur la valeur en memoire :
             | deplacer un eleve vers une classe d'un autre etablissement
             | deplacerait sinon l'eleve sans deplacer son rattachement, et
             | l'eleve deviendrait invisible des deux cotes.
             */
            if ($resolu !== null) {
                $modele->etablissement_id = $resolu;
            }
        });
    }

    /**
     * Faut-il (re)calculer le rattachement avant cet enregistrement ?
     *
     * Par defaut, seulement s'il manque. Un modele qui tient son
     * rattachement d'une autre colonne redefinit cette methode pour
     * surveiller aussi cette colonne.
     */
    public function etablissementDoitEtreResolu(): bool
    {
        return $this->etablissement_id === null;
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    /**
     * Etablissement auquel cette entite appartient.
     *
     * Par defaut : le perimetre de la requete en cours. Les modeles portes
     * par une classe (Etudiant, CandidatLibre) redefinissent cette methode
     * pour le tenir de leur classe, qui est la source autoritaire.
     */
    public function resoudreEtablissementId(): ?int
    {
        if ($this->etablissement_id !== null) {
            return (int) $this->etablissement_id;
        }

        return app(EtablissementContext::class)->idPourCreation();
    }
}
