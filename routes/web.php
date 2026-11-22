<?php

use App\Http\Controllers\CandidatLibreController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EtudiantPhotoController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\UtilisateurController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes de l'application
|--------------------------------------------------------------------------
|
| Chaque route porte la permission qu'elle exige, via le middleware can:.
| C'est la seule barriere qui compte : masquer un bouton dans une vue est un
| confort d'affichage, pas une protection.
|
| Les routes qui agissent sur une classe ou un eleve precis delegent en plus
| a une Policy (ClassePolicy, EtudiantPolicy), car la permission seule ne dit
| rien du perimetre : un operateur photo n'a acces qu'aux classes qui lui
| sont assignees.
|
*/

// Route de bienvenue
Route::get('/welcome', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
 | Ces trois routes etaient ouvertes a tout visiteur : elles exposaient les
 | effectifs de l'etablissement sans authentification.
 */
Route::middleware(['auth', 'can:' . Permissions::STATISTIQUES_VOIR])->group(function () {
    Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('api.stats');
    Route::get('/api/stats/classe/{classe}', [DashboardController::class, 'getClasseStats'])->name('api.stats.classe');
    Route::get('/api/quick-stats', [DashboardController::class, 'quickStats'])->name('api.quick-stats');
});

// Routes principales de l'application
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* ---- Classes ---------------------------------------------------- */
    Route::get('/classes', [ClasseController::class, 'index'])
        ->middleware('can:' . Permissions::CLASSES_VOIR)->name('classes.index');
    Route::get('/classes/create', [ClasseController::class, 'create'])
        ->middleware('can:' . Permissions::CLASSES_CREER)->name('classes.create');
    Route::post('/classes', [ClasseController::class, 'store'])
        ->middleware('can:' . Permissions::CLASSES_CREER)->name('classes.store');
    Route::get('/classes/{classe}', [ClasseController::class, 'show'])
        ->middleware('can:view,classe')->name('classes.show');
    Route::get('/classes/{classe}/edit', [ClasseController::class, 'edit'])
        ->middleware('can:update,classe')->name('classes.edit');
    Route::put('/classes/{classe}', [ClasseController::class, 'update'])
        ->middleware('can:update,classe')->name('classes.update');
    Route::delete('/classes/{classe}', [ClasseController::class, 'destroy'])
        ->middleware('can:delete,classe')->name('classes.destroy');

    /* ---- Import de listes -------------------------------------------- */
    Route::prefix('import')->name('import.')
        ->middleware('can:' . Permissions::ELEVES_IMPORTER)
        ->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::post('/upload', [ImportController::class, 'upload'])->name('upload');
            Route::get('/template', [ImportController::class, 'template'])->name('template');
        });

    /* ---- Production de documents -------------------------------------- */
    Route::prefix('cards')->name('cards.')->group(function () {
        Route::get('/', [CardController::class, 'index'])
            ->middleware('can:' . Permissions::DOCUMENTS_GENERER)->name('index');

        Route::post('/generate/{classe}', [CardController::class, 'generateCards'])
            ->middleware('can:genererDocuments,classe')->name('generate');
        Route::post('/processed-photos/{classe}', [CardController::class, 'generateProcessedPhotos'])
            ->middleware('can:genererDocuments,classe')->name('generate-processed-photos');
        Route::post('/printable-photos/{classe}', [CardController::class, 'generatePrintablePhotos'])
            ->middleware('can:genererDocuments,classe')->name('generate-printable-photos');
        Route::post('/generate-all/{classe}', [CardController::class, 'generateAll'])
            ->middleware('can:genererDocuments,classe')->name('generate-all');

        Route::post('/generate-all-global', [CardController::class, 'generateAllGlobal'])
            ->middleware('can:' . Permissions::DOCUMENTS_GENERER_GLOBAL)->name('generate-all-global');
    });

    /* ---- Statistiques ------------------------------------------------- */
    Route::prefix('statistics')->name('statistics.')
        ->middleware('can:' . Permissions::STATISTIQUES_VOIR)
        ->group(function () {
            Route::get('/', [StatisticsController::class, 'index'])->name('index');
            Route::get('/no-photos', [StatisticsController::class, 'noPhotos'])->name('no-photos');
        });

    /* ---- Photos des eleves -------------------------------------------- */
    Route::prefix('etudiants')->name('etudiants.')->group(function () {
        Route::post('/{etudiant}/photo', [EtudiantPhotoController::class, 'uploadPhoto'])
            ->middleware('can:gererPhoto,etudiant')->name('photo.upload');
        Route::delete('/{etudiant}/photo', [EtudiantPhotoController::class, 'deletePhoto'])
            ->middleware('can:supprimerPhoto,etudiant')->name('photo.delete');

        Route::post('/photos/bulk-upload', [EtudiantPhotoController::class, 'bulkUpload'])
            ->middleware('can:' . Permissions::PHOTOS_IMPORTER)->name('photos.bulk');

        Route::post('/{etudiant}/upload-photo', [EtudiantController::class, 'uploadPhoto'])
            ->middleware('can:gererPhoto,etudiant')->name('uploadPhoto');
        Route::post('/{etudiant}/capture-photo', [EtudiantController::class, 'capturePhoto'])
            ->middleware('can:gererPhoto,etudiant')->name('capture-photo');
    });

    /* ---- Eleves d'une classe ------------------------------------------ */
    Route::prefix('classes/{classe}')->group(function () {
        Route::post('/etudiants/transfert', [TransfertController::class, 'store'])
            ->middleware(['can:view,classe', 'can:' . Permissions::ELEVES_TRANSFERER])
            ->name('etudiants.transfert');

        Route::get('/etudiants/create', [EtudiantController::class, 'create'])
            ->middleware(['can:view,classe', 'can:' . Permissions::ELEVES_CREER])
            ->name('etudiants.create');
        Route::post('/etudiants', [EtudiantController::class, 'store'])
            ->middleware(['can:view,classe', 'can:' . Permissions::ELEVES_CREER])
            ->name('etudiants.store');

        Route::get('/etudiants/{etudiant}/edit', [EtudiantController::class, 'edit'])
            ->middleware('can:update,etudiant')->name('etudiants.edit');
        Route::put('/etudiants/{etudiant}', [EtudiantController::class, 'update'])
            ->middleware('can:update,etudiant')->name('etudiants.update');
        Route::delete('/etudiants/{etudiant}', [EtudiantController::class, 'destroy'])
            ->middleware('can:delete,etudiant')->name('etudiants.destroy');
    });

    /* ---- Candidats libres --------------------------------------------- */
    Route::prefix('classes/{classe}')->group(function () {
        Route::get('/candidats-libres', [CandidatLibreController::class, 'index'])
            ->middleware(['can:view,classe', 'can:' . Permissions::CANDIDATS_VOIR])
            ->name('candidats-libres.index');
        Route::get('/candidats-libres/create', [CandidatLibreController::class, 'create'])
            ->middleware(['can:view,classe', 'can:' . Permissions::CANDIDATS_CREER])
            ->name('candidats-libres.create');
        Route::post('/candidats-libres', [CandidatLibreController::class, 'store'])
            ->middleware(['can:view,classe', 'can:' . Permissions::CANDIDATS_CREER])
            ->name('candidats-libres.store');

        Route::get('/candidats-libres/{candidatLibre}/edit', [CandidatLibreController::class, 'edit'])
            ->middleware('can:update,candidatLibre')->name('candidats-libres.edit');
        Route::put('/candidats-libres/{candidatLibre}', [CandidatLibreController::class, 'update'])
            ->middleware('can:update,candidatLibre')->name('candidats-libres.update');
        Route::delete('/candidats-libres/{candidatLibre}', [CandidatLibreController::class, 'destroy'])
            ->middleware('can:delete,candidatLibre')->name('candidats-libres.destroy');

        Route::post('/candidats-libres/{candidatLibre}/upload-photo', [CandidatLibreController::class, 'uploadPhoto'])
            ->middleware('can:gererPhoto,candidatLibre')->name('candidats-libres.upload-photo');
        Route::post('/candidats-libres/{candidatLibre}/capture-photo', [CandidatLibreController::class, 'capturePhoto'])
            ->middleware('can:gererPhoto,candidatLibre')->name('candidats-libres.capture-photo');

        Route::get('/candidats-libres/photos-4x4', [CandidatLibreController::class, 'generate4x4'])
            ->middleware('can:genererDocuments,classe')->name('candidats-libres.photos-4x4');
        Route::get('/candidats-libres/photos-pdf', [CandidatLibreController::class, 'generatePDF'])
            ->middleware('can:genererDocuments,classe')->name('candidats-libres.photos-pdf');
    });

    /* ---- Administration ------------------------------------------------ */
    Route::prefix('administration')->name('administration.')->group(function () {

        Route::middleware('can:' . Permissions::COMPTES_GERER)->group(function () {
            Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/create', [UtilisateurController::class, 'create'])->name('utilisateurs.create');
            Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
            Route::get('/utilisateurs/{utilisateur}/edit', [UtilisateurController::class, 'edit'])->name('utilisateurs.edit');
            Route::put('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'update'])->name('utilisateurs.update');
            Route::delete('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'destroy'])->name('utilisateurs.destroy');

            Route::get('/roles', [UtilisateurController::class, 'roles'])->name('roles.index');
            Route::put('/roles/{role}', [UtilisateurController::class, 'majRole'])->name('roles.update');
        });

        /* ---- Etablissements -------------------------------------------- */
        Route::middleware('can:' . Permissions::ETABLISSEMENTS_VOIR)->group(function () {
            Route::get('/etablissements', [EtablissementController::class, 'index'])
                ->name('etablissements.index');

            /*
             | La bascule porte ETABLISSEMENTS_VOIR et non GERER : regarder
             | un autre etablissement n'est pas l'administrer. Le refus des
             | identifiants hors portee est dans EtablissementContext.
             */
            Route::post('/etablissements/basculer', [EtablissementController::class, 'basculer'])
                ->name('etablissements.basculer');

            Route::get('/etablissements/{etablissement}/edit', [EtablissementController::class, 'edit'])
                ->middleware('can:update,etablissement')->name('etablissements.edit');
            Route::put('/etablissements/{etablissement}', [EtablissementController::class, 'update'])
                ->middleware('can:update,etablissement')->name('etablissements.update');
        });

        /*
         | Creation et suppression exigent la portee globale, verifiee par
         | EtablissementPolicy : la permission seule ne suffit pas.
         */
        Route::middleware('can:' . Permissions::ETABLISSEMENTS_GERER)->group(function () {
            Route::get('/etablissements/create', [EtablissementController::class, 'create'])
                ->middleware('can:create,' . \App\Models\Etablissement::class)->name('etablissements.create');
            Route::post('/etablissements', [EtablissementController::class, 'store'])
                ->middleware('can:create,' . \App\Models\Etablissement::class)->name('etablissements.store');
            Route::delete('/etablissements/{etablissement}', [EtablissementController::class, 'destroy'])
                ->middleware('can:delete,etablissement')->name('etablissements.destroy');
        });

        Route::middleware('can:' . Permissions::JOURNAL_CONSULTER)->group(function () {
            Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
            Route::get('/journal/{entree}', [JournalController::class, 'show'])->name('journal.show');
        });
    });
});

require __DIR__ . '/auth.php';
