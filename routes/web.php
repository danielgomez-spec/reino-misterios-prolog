<?php

use App\Http\Controllers\PrologController;
use Illuminate\Support\Facades\Route;

// Páginas del juego
Route::get('/',              [PrologController::class, 'intro'])->name('game.intro');
Route::get('/personajes',    [PrologController::class, 'index'])->name('game.index');
Route::get('/misiones',      [PrologController::class, 'missions'])->name('game.missions');
Route::get('/combate/setup', [PrologController::class, 'battleSetup'])->name('battle.setup');
Route::get('/combate',       [PrologController::class, 'battle'])->name('game.battle');

// APIs AJAX
Route::prefix('api')->group(function () {
    Route::post('/combate/individual', [PrologController::class, 'combateIndividual']);
    Route::post('/combate/grupal',     [PrologController::class, 'combateGrupal']);
    Route::post('/mision',             [PrologController::class, 'misionDisponible']);
    Route::post('/inventario',         [PrologController::class, 'inventario']);
    Route::post('/xp',                 [PrologController::class, 'xpAcumulada']);
});