<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Services\PhotoStorageService;
use App\Support\EtablissementContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClasseController extends Controller
{
    public function __construct(
        private readonly PhotoStorageService $photos,
        private readonly EtablissementContext $contexte,
    ) {
    }

    public function index()
    {
        /* visiblesPar() limite la liste au perimetre du compte connecte */
        $classes = Classe::visiblesPar()->withCount('etudiants')->orderBy('nom')->get();
        return view('classes.index', compact('classes'));
    }
    
    public function create()
    {
        return view('classes.create', [
            /*
             | Le choix n'est propose que s'il est necessaire : un compte
             | rattache a un etablissement, ou un compte global qui en
             | regarde un seul, n'a rien a decider.
             */
            'etablissements' => $this->contexte->idPourCreation() === null
                ? $this->contexte->disponibles()
                : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->regles($request),
            $this->messages()
        );

        $etablissementId = $this->etablissementChoisi($request);

        if ($etablissementId === null) {
            return back()->withInput()->with(
                'error',
                'Choisissez l\'établissement auquel rattacher cette classe.'
            );
        }

        $classe = new Classe($validated);
        $classe->etablissement_id = $etablissementId;
        $classe->save();

        return redirect()->route('classes.index')->with('success', 'Classe créée avec succès');
    }
    
    public function show(Classe $classe)
    {
        $classe->load(['etudiants' => function($query) {
            $query->orderBy('nom');
        }]);

        // Classes disponibles comme destination de transfert
        $autresClasses = Classe::where('id', '!=', $classe->id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'annee_scolaire']);

        return view('classes.show', compact('classe', 'autresClasses'));
    }
    
    public function edit(Classe $classe)
    {
        return view('classes.edit', compact('classe'));
    }
    
    public function update(Request $request, Classe $classe)
    {
        $validated = $request->validate(
            $this->regles($request, $classe),
            $this->messages()
        );

        /*
         | Le SEGMENT, pas le nom : le chemin de stockage comprend desormais
         | le prefixe de l'etablissement. Comparer les noms seuls suffisait
         | tant qu'il n'y avait qu'un etablissement.
         */
        $ancienSegment = $classe->segment_photos;

        $classe->update($validated);
        $classe->refresh();

        /*
         | Le nom de la classe fait partie du chemin de stockage des photos
         | (photos/{etablissement}/{classe}/...). Sans ce deplacement, un
         | simple renommage rendait introuvables toutes les photos.
         */
        if ($ancienSegment !== $classe->segment_photos) {
            $this->photos->renameClassFolders($ancienSegment, $classe->segment_photos);
        }

        return redirect()->route('classes.show', $classe)->with('success', 'Classe mise à jour');
    }

    public function destroy(Classe $classe)
    {
        $classe->delete();
        return redirect()->route('classes.index')->with('success', 'Classe supprimée');
    }

    /* ====================================================================
     | Validation
     * ================================================================== */

    /**
     * Regles de validation d'une classe.
     *
     * L'unicite du nom est desormais portee par (etablissement, annee, nom)
     * et non par le nom seul. Deux defauts disparaissent du meme coup : deux
     * etablissements peuvent avoir chacun leur « Tle A4 », et un meme
     * etablissement peut reconduire une classe d'une annee sur la suivante.
     *
     * @return array<string, mixed>
     */
    private function regles(Request $request, ?Classe $classe = null): array
    {
        $etablissementId = $classe
            ? $classe->etablissement_id
            : $this->etablissementChoisi($request);

        return [
            'nom' => [
                'required', 'string', 'max:255',
                Rule::unique('classes', 'nom')
                    ->where(fn ($q) => $q
                        ->where('etablissement_id', $etablissementId)
                        ->where('annee_scolaire', $request->input('annee_scolaire')))
                    ->ignore($classe?->id),
            ],
            'exam' => ['nullable', 'string', 'max:255'],
            'annee_scolaire' => ['required', 'string', 'max:20'],
            'etablissement_id' => ['nullable', 'integer', Rule::exists('etablissements', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la classe est obligatoire.',
            'nom.unique' => 'Cette classe existe déjà pour cette année scolaire.',
            'annee_scolaire.required' => 'L\'année scolaire est obligatoire.',
        ];
    }

    /**
     * Etablissement de rattachement d'une classe creee maintenant.
     *
     * L'identifiant eventuellement poste est verifie contre la portee du
     * compte : sans cette verification, un champ cache suffirait a creer une
     * classe chez un etablissement auquel on n'a pas acces.
     */
    private function etablissementChoisi(Request $request): ?int
    {
        $pose = $request->input('etablissement_id');

        if ($pose !== null && $pose !== '') {
            $id = (int) $pose;

            return $request->user()?->peutAccederEtablissement($id) ? $id : null;
        }

        return $this->contexte->idPourCreation();
    }
}