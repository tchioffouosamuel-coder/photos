<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtudiantPhotoController;

Route::middleware(['api'])->group(function () {
    Route::post('/etudiants/{id}/upload-photo', [EtudiantPhotoController::class, 'uploadPhoto']);
    Route::delete('/etudiants/{id}/delete-photo', [EtudiantPhotoController::class, 'deletePhoto']);
});