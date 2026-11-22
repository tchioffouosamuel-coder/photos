<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Scopes\PorteeEtablissement;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\EtablissementContext;
use App\Support\Permissions as P;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/*
 | Gestion des comptes et des roles.
 |
 | Les garde-fous (dernier administrateur, auto-modification) sont dans
 | UserPolicy : ils doivent valoir quelle que soit la porte d'entree, et pas
 | seulement pour les formulaires de ce controleur.
 */
class UtilisateurController extends Controller
{
    public function __construct(
        private readonly AuditLogger $journal,
        private readonly EtablissementContext $contexte,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $utilisateurs = User::with(['role', 'etablissement', 'classes:id,nom'])
            ->tap(fn ($q) => $this->limiterAuPerimetre($q))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = '%' . $request->string('recherche') . '%';
                $q->where(fn ($s) => $s->where('name', 'like', $terme)->orWhere('email', 'like', $terme));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->integer('role')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('administration.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'roles' => Role::withCount('users')->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $etablissementId = $this->contexte->idPourCreation();

        return view('administration.utilisateurs.create', [
            'roles' => Role::orderBy('nom')->get(),
            'classes' => $this->classesDe($etablissementId),
            'etablissements' => $this->contexte->disponibles(),
            'etablissementParDefaut' => $etablissementId,
            'peutChangerEtablissement' => request()->user()?->aPorteeGlobale() ?? false,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $valide = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'etablissement_id' => ['nullable', 'integer', Rule::exists('etablissements', 'id')],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['integer', Rule::exists('classes', 'id')],
        ], $this->messages());

        $etablissementId = $this->etablissementChoisi($request);

        $utilisateur = User::create([
            'name' => $valide['name'],
            'email' => $valide['email'],
            'password' => $valide['password'],
            'role_id' => $valide['role_id'],
            'etablissement_id' => $etablissementId,
        ]);

        $utilisateur->classes()->sync(
            $this->classesAssignables($valide['classes'] ?? [], $etablissementId)
        );

        return redirect()
            ->route('administration.utilisateurs.index')
            ->with('success', "Le compte de {$utilisateur->name} a été créé.");
    }

    public function edit(User $utilisateur)
    {
        $this->authorize('update', $utilisateur);

        return view('administration.utilisateurs.edit', [
            'utilisateur' => $utilisateur->load('classes'),
            'roles' => Role::orderBy('nom')->get(),

            /*
             | Les classes proposees sont celles de l'etablissement DU COMPTE
             | EDITE, pas celles que l'administrateur regarde en ce moment :
             | assigner a un operateur une classe d'un autre etablissement
             | n'aurait aucun sens, et lui ouvrirait ce qu'il ne doit pas voir.
             */
            'classes' => $this->classesDe($utilisateur->etablissement_id),

            'etablissements' => $this->contexte->disponibles(),
            'peutChangerRole' => auth()->user()?->can('changerRole', $utilisateur) ?? false,
            'peutChangerEtablissement' => auth()->user()?->can('changerEtablissement', $utilisateur) ?? false,
        ]);
    }

    public function update(Request $request, User $utilisateur)
    {
        $this->authorize('update', $utilisateur);

        $regles = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($utilisateur->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['integer', Rule::exists('classes', 'id')],
        ];

        $peutChangerRole = $request->user()->can('changerRole', $utilisateur);
        $peutChangerEtablissement = $request->user()->can('changerEtablissement', $utilisateur);

        if ($peutChangerRole) {
            $regles['role_id'] = ['required', 'integer', Rule::exists('roles', 'id')];
        }

        if ($peutChangerEtablissement) {
            $regles['etablissement_id'] = ['nullable', 'integer', Rule::exists('etablissements', 'id')];
        }

        $valide = $request->validate($regles, $this->messages());

        $ancienRole = $utilisateur->role?->nom;

        $utilisateur->name = $valide['name'];
        $utilisateur->email = $valide['email'];

        if (! empty($valide['password'])) {
            $utilisateur->password = $valide['password'];
        }

        if ($peutChangerRole && (int) $valide['role_id'] !== (int) $utilisateur->role_id) {
            $utilisateur->role_id = (int) $valide['role_id'];

            /*
             | Trace dediee : un changement de niveau d'acces merite mieux
             | qu'une ligne « modification » noyee parmi les autres champs.
             */
            $this->journal->enregistrer(
                AuditLogger::ROLE_MODIFIE,
                $utilisateur,
                ['role' => $ancienRole],
                ['role' => Role::find($valide['role_id'])?->nom],
                $utilisateur->name
            );
        }

        /*
         | Le rattachement ne change que si l'autorisation existe. Le champ
         | est masque sinon, mais un formulaire poste a la main contournerait
         | un simple masquage : c'est ici que la decision se prend.
         */
        if ($peutChangerEtablissement) {
            $nouveau = array_key_exists('etablissement_id', $valide) && $valide['etablissement_id'] !== null
                ? (int) $valide['etablissement_id']
                : null;

            if ((int) $utilisateur->etablissement_id !== (int) $nouveau) {
                $this->journal->enregistrer(
                    AuditLogger::MODIFICATION,
                    $utilisateur,
                    ['etablissement' => $utilisateur->etablissement?->nom ?? 'Tous les établissements'],
                    ['etablissement' => $nouveau === null
                        ? 'Tous les établissements'
                        : Etablissement::find($nouveau)?->nom],
                    $utilisateur->name
                );

                $utilisateur->etablissement_id = $nouveau;

                /*
                 | Les classes assignees appartenaient a l'ancien
                 | etablissement : les conserver donnerait au compte un acces
                 | residuel a un etablissement dont il vient de sortir.
                 */
                $utilisateur->classes()->detach();
            }
        }

        $utilisateur->save();

        $utilisateur->classes()->sync(
            $this->classesAssignables($valide['classes'] ?? [], $utilisateur->etablissement_id)
        );

        return redirect()
            ->route('administration.utilisateurs.index')
            ->with('success', "Le compte de {$utilisateur->name} a été mis à jour.");
    }

    public function destroy(User $utilisateur)
    {
        $this->authorize('delete', $utilisateur);

        $nom = $utilisateur->name;
        $utilisateur->delete();

        return redirect()
            ->route('administration.utilisateurs.index')
            ->with('success', "Le compte de {$nom} a été supprimé. Ses traces restent au journal.");
    }

    /* ====================================================================
     | Roles
     * ================================================================== */

    public function roles()
    {
        $this->authorize('viewAny', User::class);

        return view('administration.roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('nom')->get(),
            'groupes' => P::parGroupe(),
        ]);
    }

    public function majRole(Request $request, Role $role)
    {
        $this->authorize('viewAny', User::class);

        if ($role->estAdministrateur()) {
            return back()->with(
                'error',
                'Les permissions de l\'administrateur ne sont pas modifiables : '
                . 'ce rôle est le recours si une dotation devient incohérente.'
            );
        }

        $valide = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(P::codes())],
        ]);

        $avant = $role->permissions->pluck('code')->sort()->values()->all();
        $apres = array_values($valide['permissions'] ?? []);

        $ids = Permission::whereIn('code', $apres)->pluck('id')->all();
        $role->permissions()->sync($ids);

        $this->journal->enregistrer(
            AuditLogger::MODIFICATION,
            $role,
            ['permissions' => $avant],
            ['permissions' => $apres],
            'Rôle : ' . $role->nom
        );

        return back()->with('success', "Les permissions du rôle {$role->nom} ont été enregistrées.");
    }

    /* ====================================================================
     | Perimetre
     * ================================================================== */

    /**
     * Limite une requete de comptes au perimetre de celui qui regarde.
     *
     * Les comptes ne portent pas de portee globale de requete (voir la note
     * de UserPolicy) : le filtrage des LISTES se fait donc ici, et le
     * controle d'acces a UN compte dans la Policy. Les deux sont
     * necessaires : sans ce filtre, un administrateur d'ecole verrait les
     * noms et adresses de tous les comptes du reseau, meme sans pouvoir les
     * ouvrir.
     */
    private function limiterAuPerimetre($requete): void
    {
        $ids = $this->contexte->idsAutorises();

        if ($ids === null) {
            return;
        }

        /* Les comptes a portee globale restent invisibles d'ici. */
        $requete->whereIn('etablissement_id', $ids);
    }

    /**
     * Classes d'un etablissement, pour le selecteur d'assignation.
     *
     * La portee globale de requete est retiree a dessein : on veut les
     * classes de l'etablissement du compte edite, pas celles de
     * l'etablissement que l'administrateur regarde en ce moment.
     */
    private function classesDe(?int $etablissementId)
    {
        if ($etablissementId === null) {
            /*
             | Compte a portee globale : aucune assignation de classe n'a de
             | sens, il voit tout de toute facon.
             */
            return collect();
        }

        return Classe::withoutGlobalScope(PorteeEtablissement::class)
            ->where('etablissement_id', $etablissementId)
            ->orderBy('nom')
            ->get(['id', 'nom', 'annee_scolaire']);
    }

    /**
     * Ne retient que les classes appartenant reellement a l'etablissement du
     * compte.
     *
     * La validation verifie que les identifiants existent, pas qu'ils sont
     * legitimes. Sans ce filtre, un identifiant de classe ajoute a la main
     * dans le formulaire donnerait a un operateur l'acces a la classe d'un
     * autre etablissement — c'est-a-dire a ses eleves et a leurs photos.
     *
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function classesAssignables(array $ids, ?int $etablissementId): array
    {
        if ($ids === [] || $etablissementId === null) {
            return [];
        }

        return Classe::withoutGlobalScope(PorteeEtablissement::class)
            ->whereIn('id', $ids)
            ->where('etablissement_id', $etablissementId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Etablissement de rattachement d'un compte cree maintenant.
     *
     * Un identifiant poste n'est retenu que si celui qui agit a lui-meme la
     * portee globale : autrement, creer un compte serait un moyen detourne
     * de placer quelqu'un chez le voisin.
     */
    private function etablissementChoisi(Request $request): ?int
    {
        $acteur = $request->user();

        if (! $acteur?->aPorteeGlobale()) {
            return $acteur?->etablissement_id === null ? null : (int) $acteur->etablissement_id;
        }

        $pose = $request->input('etablissement_id');

        return ($pose === null || $pose === '') ? null : (int) $pose;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation ne correspond pas au mot de passe.',
            'role_id.required' => 'Le rôle est obligatoire.',
            'role_id.exists' => 'Ce rôle est introuvable.',
        ];
    }
}
