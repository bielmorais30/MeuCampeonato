<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTeamsRequest;
use App\Http\Requests\RegisterMultipleTeamsRequest;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\Registration;
use App\Models\Standing;
use App\Models\Team;
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

        $this->startChampionshipIfReady($championship);

        $response = [
            'message' => 'Equipe registrada com sucesso no campeonato: ' . $championship->name,
        ];

        return response()->json($response, 201);
    }

    public function registerMultiple(RegisterMultipleTeamsRequest $request, Championship $championship)
    {
        $registeredTeamIds = [];
        
        foreach ($request->team_ids as $teamId) {
            Registration::create([
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

            $registeredTeamIds[] = $teamId;
        }

        $teamNamesById = Team::whereIn('id', $registeredTeamIds)
            ->pluck('name', 'id');

        $registrations = collect($registeredTeamIds)
            ->map(fn ($teamId) => $teamNamesById->get($teamId))
            ->filter()
            ->values();

        $this->startChampionshipIfReady($championship);

        $response = [
            'message' => count($registrations) . ' equipes registradas com sucesso no campeonato: ' . $championship->name,
            'registrations' => $registrations,
        ];

        return response()->json($response, 201);
    }

    private function startChampionshipIfReady(Championship $championship): void
    {
        $totalTeams = Registration::where('championship_id', $championship->id)->count();

        if ($totalTeams !== 8) {
            return;
        }

        if ($championship->status !== 'running') {
            $championship->update(['status' => 'running']);
        }

        $hasMatches = ChampionshipMatch::where('championship_id', $championship->id)->exists();

        if ($hasMatches) {
            return;
        }

        $phases = [
            ['phase' => 'quarter', 'quantity' => 4],
            ['phase' => 'semi', 'quantity' => 2],
            ['phase' => 'third_place', 'quantity' => 1],
            ['phase' => 'final', 'quantity' => 1],
        ];

        $counter = 1;
        foreach ($phases as $phase) {
            for ($i = 0; $i < $phase['quantity']; $i++) {
                ChampionshipMatch::create([
                    'championship_id' => $championship->id,
                    'phase' => $phase['phase'],
                    'order' => $counter,
                ]);
                $counter++;
            }
        }

        Registration::where('championship_id', $championship->id)
            ->inRandomOrder()
            ->pluck('team_id')
            ->values()
            ->chunk(2)
            ->each(function ($teamIds) use ($championship) {
            $pair = $teamIds->values(); // pega os IDs dos times faz seu index começar do zero

            $match = ChampionshipMatch::where('championship_id', $championship->id)
                ->where('phase', 'quarter')
                ->whereNull('team_home_id')
                ->whereNull('team_away_id')
                ->first();

            if ($match) {
                $match->update([
                    'team_home_id' => $pair->get(0),
                    'team_away_id' => $pair->get(1),
                ]);
            }
        });
    }
}
