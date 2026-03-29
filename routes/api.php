<?php

use App\Http\Controllers\ChampionshipsController;
use App\Http\Controllers\RegistrationsController;
use App\Http\Controllers\TeamsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('championships', ChampionshipsController::class);
Route::apiResource('teams', TeamsController::class);

Route::prefix('championships/{championship}/')->group(function () {
    Route::post('register', [RegistrationsController::class, 'register']);
    Route::post('register-multiple', [RegistrationsController::class, 'registerMultiple']);
});
