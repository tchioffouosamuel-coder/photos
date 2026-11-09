<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Services\PhotoStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/*
 | Transfert d'eleves d'une classe vers une autre.
 |
 | Le nom de la classe fait partie du chemin de stockage des photos
 | (photos/{classe}/{fichier}), le transfert deplace donc aussi les fichiers
 | via PhotoStorageService. L'ensemble est fait dans une transaction : si un
 | deplacement de fichier echoue, l'eleve reste rattache a sa classe d'origine.
 */
class TransfertController extends Controller
{
    public function __construct(private readonly PhotoStorageService $photos)
    {
    }

    /**
     * Transfere un ou plusieurs eleves de $classe vers la classe de destination.
     */
    public function store(Request $request, Classe $classe)
    {
        $validated = $request->validate([
            'classe_destination_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id'),
                Rule::notIn([$classe->id]),
            ],
            'etudiants' => ['required', 'array', 'min:1'],
            'etudiants.*' => ['integer', Rule::exists('etudiants', 'id')],
        ], [
            'classe_destination_id.required' => 'Veuillez choisir la classe de destination.',
            'classe_destination_id.not_in' => 'La classe de destination doit etre differente de la classe actuelle.',
            'classe_destination_id.exists' => 'La classe de destination est introuvable.',
            'etudiants.required' => 'Veuillez selectionner au moins un eleve a transferer.',
            'etudiants.min' => 'Veuillez selectionner au moins un eleve a transferer.',
        ]);

        $destination = Classe::findOrFail($validated['classe_destination_id']);

        /*
         | On ne transfere que les eleves appartenant reellement a la classe
         | d'origine : cela neutralise une selection forgee ou obsolete.
         */
        $etudiants = Etudiant::whereIn('id', $validated['etudiants'])
            ->where('classe_id', $classe->id)
            ->get();

        if ($etudiants->isEmpty()) {
            return back()->with('error', "Aucun eleve valide a transferer : la selection ne correspond pas a la classe {$classe->nom}.");
        }

        $transferes = 0;
        $photosDeplacees = 0;
        $photosIntrouvables = [];

        try {
            DB::transaction(function () use ($etudiants, $classe, $destination, &$transferes, &$photosDeplacees, &$photosIntrouvables) {
                foreach ($etudiants as $etudiant) {
                    $photoOrigine = $etudiant->photo;

                    if (! empty($photoOrigine)) {
                        $nouveauFichier = $this->photos->moveStudentPhoto(
                            $etudiant,
                            $classe->nom,
                            $destination->nom
                        );

                        if ($nouveauFichier === null) {
                            // Le fichier est introuvable : l'eleve est transfere
                            // sans photo plutot que de pointer vers un chemin mort.
                            $etudiant->photo = null;
                            $photosIntrouvables[] = trim($etudiant->nom . ' ' . $etudiant->prenoms);
                        } else {
                            $etudiant->photo = $nouveauFichier;
                            $photosDeplacees++;
                        }
                    }

                    $etudiant->classe_id = $destination->id;
                    $etudiant->save();
                    $transferes++;
                }
            });
        } catch (\Throwable $e) {
            Log::error('[transfert] echec', [
                'classe_source' => $classe->id,
                'classe_destination' => $destination->id,
                'erreur' => $e->getMessage(),
            ]);

            return back()->with('error', 'Le transfert a echoue : ' . $e->getMessage());
        }

        Log::info('[transfert] eleves transferes', [
            'de' => $classe->nom,
            'vers' => $destination->nom,
            'eleves' => $transferes,
            'photos_deplacees' => $photosDeplacees,
        ]);

        $message = $transferes === 1
            ? "1 eleve transfere vers {$destination->nom}."
            : "{$transferes} eleves transferes vers {$destination->nom}.";

        if ($photosDeplacees > 0) {
            $message .= $photosDeplacees === 1
                ? ' 1 photo deplacee.'
                : " {$photosDeplacees} photos deplacees.";
        }

        $response = redirect()
            ->route('classes.show', $destination)
            ->with('success', $message);

        if ($photosIntrouvables !== []) {
            $noms = implode(', ', array_slice($photosIntrouvables, 0, 5));
            $suite = count($photosIntrouvables) > 5
                ? ' et ' . (count($photosIntrouvables) - 5) . ' autre(s)'
                : '';

            $response->with(
                'warning',
                "Photo introuvable sur le disque pour : {$noms}{$suite}. Ces eleves ont ete transferes sans photo."
            );
        }

        return $response;
    }
}
