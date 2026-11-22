<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Scopes\PorteeEtablissement;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\EtablissementContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/*
 | Gestion des etablissements.
 |
 | Les garde-fous (portee globale exigee, dernier etablissement, etablissement
 | non vide) sont dans EtablissementPolicy : ils doivent valoir quelle que
 | soit la porte d'entree.
 |
 | Les comptages affiches ici retirent la portee globale de requete : on veut
 | savoir ce que chaque etablissement contient reellement, y compris celui
 | qu'on ne regarde pas en ce moment.
 */
class EtablissementController extends Controller
{
    public function __construct(
        private readonly AuditLogger $journal,
        private readonly EtablissementContext $contexte,
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', Etablissement::class);

        $etablissements = $this->contexte->disponibles();

        /* Comptages hors portee : voir la note en tete de classe. */
        $effectifs = [];

        foreach ($etablissements as $etablissement) {
            $effectifs[$etablissement->id] = [
                'classes' => Classe::withoutGlobalScope(PorteeEtablissement::class)
                    ->where('etablissement_id', $etablissement->id)->count(),
                'eleves' => Etudiant::withoutGlobalScope(PorteeEtablissement::class)
                    ->where('etablissement_id', $etablissement->id)->count(),
                'comptes' => User::where('etablissement_id', $etablissement->id)->count(),
            ];
        }

        return view('administration.etablissements.index', [
            'etablissements' => $etablissements,
            'effectifs' => $effectifs,
            'idActif' => $this->contexte->idActif(),
            'peutCreer' => request()->user()?->can('create', Etablissement::class) ?? false,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Etablissement::class);

        return view('administration.etablissements.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Etablissement::class);

        $valide = $request->validate($this->regles(), $this->messages());

        $etablissement = new Etablissement($valide);

        /*
         | Le prefixe de dossier photos est derive du code, jamais saisi : il
         | sert de nom de dossier sur le serveur, et un prefixe choisi
         | librement ouvrirait la porte a « ../ » dans un chemin de fichier.
         |
         | Il est obligatoire pour tout etablissement cree apres le passage au
         | multi-etablissement. Seul l'etablissement d'origine en est
         | dispense, parce que ses fichiers existaient avant lui.
         */
        $etablissement->prefixe_photos = $this->prefixeLibre($valide['code']);
        $etablissement->actif = true;
        $etablissement->save();

        $this->journal->creation($etablissement);

        return redirect()
            ->route('administration.etablissements.index')
            ->with('success', "L'établissement {$etablissement->nom} a été créé. Il est vide : créez-y ses classes, ou rattachez-lui un compte.");
    }

    public function edit(Etablissement $etablissement)
    {
        $this->authorize('update', $etablissement);

        return view('administration.etablissements.edit', [
            'etablissement' => $etablissement,
        ]);
    }

    public function update(Request $request, Etablissement $etablissement)
    {
        $this->authorize('update', $etablissement);

        $valide = $request->validate($this->regles($etablissement), $this->messages());

        /*
         | Le prefixe de dossier n'est PAS recalcule sur un changement de
         | code. Le recalculer rendrait introuvables toutes les photos deja
         | rangees sous l'ancien prefixe, pour un simple renommage
         | administratif. Le code imprime sur les documents change ; le
         | dossier sur le disque, non.
         */
        $etablissement->fill($valide);
        $etablissement->save();

        return redirect()
            ->route('administration.etablissements.index')
            ->with('success', "La fiche de {$etablissement->nom} a été mise à jour.");
    }

    public function destroy(Etablissement $etablissement)
    {
        $this->authorize('delete', $etablissement);

        $nom = $etablissement->nom;
        $etaitActif = $this->contexte->idActif() === (int) $etablissement->getKey();

        $etablissement->delete();

        /*
         | Si le contexte pointait sur lui, il designe maintenant une ligne
         | disparue : toutes les requetes suivantes seraient filtrees sur un
         | identifiant mort et ne renverraient plus rien.
         */
        if ($etaitActif) {
            $this->contexte->definirActif(null);
        }

        return redirect()
            ->route('administration.etablissements.index')
            ->with('success', "L'établissement {$nom} a été supprimé.");
    }

    /* ====================================================================
     | Bascule d'etablissement
     * ================================================================== */

    /**
     * Change l'etablissement regarde par un compte a portee globale.
     *
     * L'identifiant arrive de l'interface, donc d'un endroit non fiable :
     * definirActif() le verifie contre la portee du compte et refuse tout
     * ce qui en sort. On ne se contente pas de masquer le selecteur.
     */
    public function basculer(Request $request)
    {
        $valide = $request->validate([
            'etablissement_id' => ['nullable', 'integer', Rule::exists('etablissements', 'id')],
        ]);

        $id = $valide['etablissement_id'] ?? null;

        if (! $this->contexte->definirActif($id === null ? null : (int) $id)) {
            return back()->with('error', "Vous n'avez pas accès à cet établissement.");
        }

        $actif = $this->contexte->actif();

        return back()->with(
            'success',
            $actif
                ? "Vous travaillez maintenant sur {$actif->nom}."
                : 'Vous voyez maintenant tous les établissements.'
        );
    }

    /* ====================================================================
     | Validation
     * ================================================================== */

    /**
     * @return array<string, mixed>
     */
    private function regles(?Etablissement $etablissement = null): array
    {
        return [
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('etablissements', 'code')->ignore($etablissement?->id),
            ],
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:30'],
            'ville' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'code.required' => 'Le code de l\'établissement est obligatoire : il est imprimé sur les documents officiels.',
            'code.unique' => 'Ce code est déjà utilisé par un autre établissement.',
            'nom.required' => 'Le nom de l\'établissement est obligatoire.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
        ];
    }

    /**
     * Prefixe de dossier libre, derive du code.
     *
     * La colonne est unique : deux codes donnant le meme slug (« ABC/1 » et
     * « ABC-1 ») feraient echouer l'enregistrement sur une erreur de base
     * incomprehensible pour l'utilisateur. On suffixe plutot.
     */
    private function prefixeLibre(string $code): string
    {
        $base = Etablissement::prefixeDepuis($code);
        $candidat = $base;

        for ($i = 2; Etablissement::where('prefixe_photos', $candidat)->exists(); $i++) {
            $candidat = $base . '-' . $i;

            if ($i > 50) {
                $candidat = $base . '-' . uniqid();
                break;
            }
        }

        return $candidat;
    }
}
