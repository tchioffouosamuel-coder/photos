<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\Request;

/*
 | Consultation du journal d'activite.
 |
 | Lecture seule, sans exception : aucune action de ce controleur ne modifie
 | ni ne supprime une entree. Le modele le refuse de toute facon, mais
 | l'absence de route d'ecriture est la premiere garantie.
 |
 | Le cloisonnement par etablissement n'apparait pas dans ce fichier : il est
 | porte par une portee globale sur ActivityLog. C'est volontaire — un filtre
 | a ecrire ici serait un filtre a oublier, et l'oublier donnerait a un
 | etablissement le detail de l'activite d'un autre, adresses IP comprises.
 */
class JournalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permissions::JOURNAL_CONSULTER);

        $entrees = ActivityLog::query()
            ->with('user:id,name')
            ->when($request->filled('auteur'), fn ($q) => $q->deLAuteur($request->integer('auteur')))
            ->when($request->filled('action'), fn ($q) => $q->action($request->string('action')))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = '%' . $request->string('recherche') . '%';
                $q->where(fn ($s) => $s->where('sujet_libelle', 'like', $terme)
                    ->orWhere('user_nom', 'like', $terme));
            })
            ->entre($request->input('du'), $request->input('au'))
            ->latest('created_at')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('administration.journal.index', [
            'entrees' => $entrees,

            /*
             | Les auteurs proposes au filtre sont ceux qui ont reellement
             | agi DANS le perimetre visible, et non la liste des comptes :
             | lister tous les comptes du reseau dans un selecteur
             | divulguerait leurs noms a chaque etablissement.
             */
            'auteurs' => User::whereIn(
                'id',
                ActivityLog::query()->whereNotNull('user_id')->distinct()->select('user_id')
            )->orderBy('name')->get(['id', 'name']),

            'actions' => $this->actionsDisponibles(),
            'resume' => $this->resume(),
        ]);
    }

    public function show(ActivityLog $entree)
    {
        $this->authorize(Permissions::JOURNAL_CONSULTER);

        return view('administration.journal.show', [
            'entree' => $entree->load(['user:id,name', 'etablissement:id,nom']),
            'differences' => $entree->differences(),
            /* Les autres traces du meme objet, pour reconstituer son histoire */
            'voisines' => $entree->sujet_type
                ? ActivityLog::surSujet($entree->sujet_type, $entree->sujet_id)
                    ->whereKeyNot($entree->getKey())
                    ->latest('created_at')
                    ->limit(20)
                    ->get()
                : collect(),
        ]);
    }

    /**
     * Actions reellement presentes en base, pour ne pas proposer un filtre
     * qui ne renverrait jamais rien.
     *
     * @return array<string, string>
     */
    private function actionsDisponibles(): array
    {
        $presentes = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $libelles = [];

        foreach ($presentes as $action) {
            $libelles[$action] = AuditLogger::libelleAction($action);
        }

        return $libelles;
    }

    /**
     * @return array{total: int, aujourdhui: int, echecs: int, auteurs: int}
     */
    private function resume(): array
    {
        return [
            'total' => ActivityLog::count(),
            'aujourdhui' => ActivityLog::whereDate('created_at', today())->count(),
            'echecs' => ActivityLog::action(AuditLogger::CONNEXION_ECHOUEE)
                ->whereDate('created_at', '>=', today()->subDays(7))
                ->count(),
            'auteurs' => ActivityLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];
    }
}
