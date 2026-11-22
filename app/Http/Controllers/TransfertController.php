<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Services\AuditLogger;
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
    public function __construct(
        private readonly PhotoStorageService $photos,
        private readonly AuditLogger $journal,
    ) {
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
            'classe_destination_id.not_in' => 'La classe de destination doit être différente de la classe actuelle.',
            'classe_destination_id.exists' => 'La classe de destination est introuvable.',
            'etudiants.required' => 'Veuillez sélectionner au moins un élève à transférer.',
            'etudiants.min' => 'Veuillez sélectionner au moins un élève à transférer.',
        ]);

        $destination = Classe::findOrFail($validated['classe_destination_id']);

        /*
         | Un transfert ne franchit pas une frontiere d'etablissement.
         |
         | La portee globale filtre deja les classes visibles, donc le cas ne
         | se presente que pour un compte a portee globale qui regarde « tous
         | les etablissements ». Pour lui, les deux classes sont a l'ecran et
         | rien ne l'empeche de les apparier. Or deplacer un eleve d'un
         | etablissement vers un autre n'est pas un transfert de classe :
         | c'est une radiation suivie d'une inscription, avec un dossier
         | administratif a la cle. L'application refuse de le faire passer
         | pour un simple changement de classe.
         */
        if ((int) $classe->etablissement_id !== (int) $destination->etablissement_id) {
            return back()->with(
                'error',
                'Les deux classes n\'appartiennent pas au même établissement. '
                . 'Un changement d\'établissement se fait par radiation puis nouvelle inscription, '
                . 'pas par un transfert de classe.'
            );
        }

        /*
         | On ne transfere que les eleves appartenant reellement a la classe
         | d'origine : cela neutralise une selection forgee ou obsolete.
         */
        $etudiants = Etudiant::whereIn('id', $validated['etudiants'])
            ->where('classe_id', $classe->id)
            ->get();

        if ($etudiants->isEmpty()) {
            return back()->with('error', "Aucun élève valide à transférer : la sélection ne correspond pas à la classe {$classe->nom}.");
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
                            $classe->segment_photos,
                            $destination->segment_photos
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
            Log::error('[transfert] échec', [
                'classe_source' => $classe->id,
                'classe_destination' => $destination->id,
                'erreur' => $e->getMessage(),
            ]);

            return back()->with('error', 'Le transfert a échoué : ' . $e->getMessage());
        }

        /*
         | Trace d'audit : un transfert deplace des eleves ET leurs fichiers
         | photo d'un dossier a l'autre. C'est l'operation la plus difficile a
         | reconstituer apres coup si elle n'est pas journalisee.
         */
        $this->journal->enregistrer(
            AuditLogger::TRANSFERT,
            $destination,
            ['classe' => $classe->nom, 'effectif_deplace' => $transferes],
            [
                'classe' => $destination->nom,
                'eleves' => $etudiants->map(fn ($e) => trim($e->nom . ' ' . $e->prenoms))->values()->all(),
                'photos_deplacees' => $photosDeplacees,
                'photos_introuvables' => $photosIntrouvables,
            ],
            "De {$classe->nom} vers {$destination->nom}"
        );

        Log::info('[transfert] élèves transférés', [
            'de' => $classe->nom,
            'vers' => $destination->nom,
            'eleves' => $transferes,
            'photos_deplacees' => $photosDeplacees,
        ]);

        $message = $transferes === 1
            ? "1 élève transféré vers {$destination->nom}."
            : "{$transferes} élèves transférés vers {$destination->nom}.";

        if ($photosDeplacees > 0) {
            $message .= $photosDeplacees === 1
                ? ' 1 photo deplacee.'
                : " {$photosDeplacees} photos déplacées.";
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
                "Photo introuvable sur le disque pour : {$noms}{$suite}. Ces élèves ont été transférés sans photo."
            );
        }

        return $response;
    }
}
