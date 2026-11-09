<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\EtudiantPhotoController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CandidatLibreController;
use App\Http\Controllers\TransfertController;
// Route de bienvenue
Route::get('/welcome', function () {
    return view('welcome');
});




// Routes API optionnelles
Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('api.stats');
Route::get('/api/stats/classe/{classe}', [DashboardController::class, 'getClasseStats'])->name('api.stats.classe');
Route::get('/api/quick-stats', [DashboardController::class, 'quickStats'])->name('api.quick-stats');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes principales de l'application
Route::middleware(['auth'])->group(function () {
    // Redirection de la home vers les classes
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes des Classes - Full CRUD
    Route::get('/classes', [ClasseController::class, 'index'])->name('classes.index');
    Route::get('/classes/create', [ClasseController::class, 'create'])->name('classes.create');
    Route::post('/classes', [ClasseController::class, 'store'])->name('classes.store');
    Route::get('/classes/{classe}', [ClasseController::class, 'show'])->name('classes.show');
    Route::get('/classes/{classe}/edit', [ClasseController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{classe}', [ClasseController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{classe}', [ClasseController::class, 'destroy'])->name('classes.destroy');

    // Routes d'importation Excel
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/upload', [ImportController::class, 'upload'])->name('upload');
        Route::get('/template', [ImportController::class, 'template'])->name('template');
    });

    Route::prefix('cards')->name('cards.')->group(function () {
        Route::get('/', [CardController::class, 'index'])->name('index');
        Route::post('/generate/{classe}', [CardController::class, 'generateCards'])->name('generate');
        Route::post('/processed-photos/{classe}', [CardController::class, 'generateProcessedPhotos'])->name('generate-processed-photos');
        Route::post('/printable-photos/{classe}', [CardController::class, 'generatePrintablePhotos'])->name('generate-printable-photos');
        Route::post('/generate-all/{classe}', [CardController::class, 'generateAll'])->name('generate-all');
        Route::post('/generate-all-global', [CardController::class, 'generateAllGlobal'])->name('generate-all-global');
    });

    // Routes des Statistiques
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/no-photos', [StatisticsController::class, 'noPhotos'])->name('no-photos');
    });

    // Routes de gestion des photos d'étudiants
    Route::prefix('etudiants')->name('etudiants.')->group(function () {
        Route::post('/{etudiant}/photo', [EtudiantPhotoController::class, 'uploadPhoto'])->name('photo.upload');
        Route::delete('/{etudiant}/photo', [EtudiantPhotoController::class, 'deletePhoto'])->name('photo.delete');
        Route::post('/photos/bulk-upload', [EtudiantPhotoController::class, 'bulkUpload'])->name('photos.bulk');
        
        // Routes d'upload de photos (DÉPLACÉES ICI)
        Route::post('/{etudiant}/upload-photo', [EtudiantController::class, 'uploadPhoto'])->name('uploadPhoto');
        Route::post('/{etudiant}/capture-photo', [EtudiantController::class, 'capturePhoto'])->name('capture-photo');
    });

    // Etudiants Routes (CRUD)
    Route::prefix('classes/{classe}')->group(function () {
        // Transfert d'un ou plusieurs eleves vers une autre classe
        Route::post('/etudiants/transfert', [TransfertController::class, 'store'])
            ->name('etudiants.transfert');

        Route::get('/etudiants/create', [EtudiantController::class, 'create'])->name('etudiants.create');
        Route::post('/etudiants', [EtudiantController::class, 'store'])->name('etudiants.store');
        Route::get('/etudiants/{etudiant}/edit', [EtudiantController::class, 'edit'])->name('etudiants.edit');
        Route::put('/etudiants/{etudiant}', [EtudiantController::class, 'update'])->name('etudiants.update');
        Route::delete('/etudiants/{etudiant}', [EtudiantController::class, 'destroy'])->name('etudiants.destroy');
    });
    

Route::middleware('auth')->prefix('classes/{classe}')->group(function () {
    
    // Candidats Libres
    Route::get('/candidats-libres', [CandidatLibreController::class, 'index'])
        ->name('candidats-libres.index');
    Route::get('/candidats-libres/create', [CandidatLibreController::class, 'create'])
        ->name('candidats-libres.create');
    Route::post('/candidats-libres', [CandidatLibreController::class, 'store'])
        ->name('candidats-libres.store');
    Route::get('/candidats-libres/{candidatLibre}/edit', [CandidatLibreController::class, 'edit'])
        ->name('candidats-libres.edit');
    Route::put('/candidats-libres/{candidatLibre}', [CandidatLibreController::class, 'update'])
        ->name('candidats-libres.update');
    Route::delete('/candidats-libres/{candidatLibre}', [CandidatLibreController::class, 'destroy'])
        ->name('candidats-libres.destroy');
    
    // Photos
    Route::post('/candidats-libres/{candidatLibre}/upload-photo', [CandidatLibreController::class, 'uploadPhoto'])
        ->name('candidats-libres.upload-photo');
    Route::post('/candidats-libres/{candidatLibre}/capture-photo', [CandidatLibreController::class, 'capturePhoto'])
        ->name('candidats-libres.capture-photo');
    
    // Documents
    Route::get('/candidats-libres/photos-4x4', [CandidatLibreController::class, 'generate4x4'])
        ->name('candidats-libres.photos-4x4');
    Route::get('/candidats-libres/photos-pdf', [CandidatLibreController::class, 'generatePDF'])
        ->name('candidats-libres.photos-pdf');
});
});

require __DIR__ . '/auth.php';