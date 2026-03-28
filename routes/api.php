<?php

use App\Http\Controllers\ChampionshipsController;
use App\Http\Controllers\TeamsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('championships', ChampionshipsController::class);
Route::apiResource('teams', TeamsController::class);
