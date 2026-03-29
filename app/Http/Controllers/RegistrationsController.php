<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTeamsRequest;
use App\Http\Requests\RegisterMultipleTeamsRequest;
use App\Models\Championship;
use App\Models\Registration;
use App\Models\Standing;
use Illuminate\Http\Request;

class RegistrationsController extends Controller
{
    public function register(RegisterTeamsRequest $request, Championship $championship)
    {
        $registration = Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $request->team_id,
        ]);

        // Crando o resgistro de pontuação para a equipe no campeonato
        Standing::create([
            'championship_id' => $championship->id,
            'team_id' => $request->team_id,
            'points' => 0,
            'matches_played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
        ]);

        // Verificar se o campeonato completou 8 times e atualizar status para 'running'
        $totalTeams = Registration::where('championship_id', $championship->id)->count();
        if ($totalTeams === 8) {
            $championship->update(['status' => 'running']);
        }

        $response = [
            'message' => 'Equipe registrada com sucesso no campeonato: ' . $championship->name,
        ];

        return response()->json($response, 201);
    }

    public function registerMultiple(RegisterMultipleTeamsRequest $request, Championship $championship)
    {
        $registrations = [];
        
        foreach ($request->team_ids as $teamId) {
            $registration = Registration::create([
                'championship_id' => $championship->id,
                'team_id' => $teamId,
            ]);

            Standing::create([
                'championship_id' => $championship->id,
                'team_id' => $teamId,
                'points' => 0,
                'matches_played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ]);

            $registrations[] = $registration;
        }

        // Verificar se o campeonato completou 8 times e atualizar status para 'running'
        $totalTeams = Registration::where('championship_id', $championship->id)->count();
        if ($totalTeams === 8) {
            $championship->update(['status' => 'running']);
        }

        $response = [
            'message' => count($registrations) . ' equipes registradas com sucesso no campeonato: ' . $championship->name,
            'registrations' => $registrations,
        ];

        return response()->json($response, 201);
    }
}
