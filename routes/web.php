<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\MatiereController;

// --- AUTHENTIFICATION ---
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- INTERFACE SÉCURISÉE ---
Route::middleware(['auth'])->group(function () {

    // Route pour le changement de mot de passe obligatoire
    Route::get('/password/change', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'updatePassword'])->name('password.update');

    // Le reste des routes est protégé : si le gestionnaire doit changer son mot de passe, il doit être redirigé
    Route::middleware([\App\Http\Middleware\CheckFirstLogin::class])->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Gestion des classes & élèves (Gestionnaire)
        Route::resource('classes', ClasseController::class);
        Route::resource('classes', ClasseController::class)->except(['create', 'edit', 'show']);

        Route::resource('eleves', EleveController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::resource('eleves', EleveController::class)->except(['create', 'edit', 'show']);
        Route::get('/classes/{id}/edit', [ClasseController::class, 'edit'])->name('classes.edit');
        Route::get('/eleves/{id}/edit', [EleveController::class, 'edit'])->name('eleves.edit');
        Route::put('/eleves/{id}', [EleveController::class, 'update'])->name('eleves.update');
        Route::delete('/eleves/{id}', [EleveController::class, 'destroy'])->name('eleves.destroy');

        // Gestion des Enseignants (Création exclusive par le Gestionnaire)
        Route::resource('enseignants', EnseignantController::class)->only(['index', 'store', 'destroy']);
        Route::get('/enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
        Route::post('/enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
        Route::delete('/enseignants/{id}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy');

        // Paiements & Notes
        Route::get('/paiements',        [PaiementController::class, 'index']) ->name('paiements.index');
        Route::post('/paiements',       [PaiementController::class, 'store']) ->name('paiements.store');
        Route::get('/paiements/{eleve}/recu', [PaiementController::class, 'telechargerRecu'])->name('paiements.recu');


        Route::get('/notes',                          [NoteController::class, 'index'])  ->name('notes.index');
        Route::get('/notes/create',                   [NoteController::class, 'create']) ->name('notes.create');
        Route::post('/notes',                         [NoteController::class, 'store'])  ->name('notes.store');
        Route::get('/notes/{eleve}/edit/{trimestre}', [NoteController::class, 'edit'])   ->name('notes.edit');
        Route::put('/notes/{eleve}/edit/{trimestre}', [NoteController::class, 'update']) ->name('notes.update');


        // Saisie des notes réservée aux enseignants
        Route::middleware([\App\Http\Middleware\CheckEnseignant::class])->group(function () {
            Route::get('/notes/saisie', [NoteController::class, 'create'])->name('notes.create');
            Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
        });

        Route::resource('matieres', MatiereController::class)->only(['index', 'store', 'destroy']);
        Route::get('classes/{id}/matieres', [ClasseController::class, 'matieres'])->name('classes.matieres');
        Route::post('classes/{id}/matieres', [ClasseController::class, 'updateMatieres'])->name('classes.matieres.update');

        // Nouvelle route : charge le formulaire d'édition comme fragment HTML (pour le modal AJAX)
        // Elle est appelée par le JavaScript dans notes/index.blade.php
        Route::get('/notes/{eleve}/edit/{trimestre}/fragment', [NoteController::class, 'editFragment'])
            ->name('notes.edit.fragment');
        // Bulletin PDF
        Route::get('/bulletin/{eleve}', [NoteController::class, 'telechargerBulletin'])->name('bulletin.telecharger');
    });
});