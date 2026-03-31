<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChampionshipRequest;
use App\Models\Championship;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateChampionshipRequest;

class ChampionshipsController extends Controller
{
    /**
     * Display a listing of the resource.
     * Agora retorna apenas todos os campeonatos, sem detalhes de times ou partidas.
     */
    public function index()
    {
        $championships = Championship::all();
        return response()->json($championships, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChampionshipRequest $request)
    {
        $championship = Championship::create($request->validated());
        return response()->json($championship, 201);
    }

    /**
     * Display the specified resource.
     * Agora retorna detalhes completos, incluindo times e partidas com vencedores.
     */
    public function show(Championship $championship)
    {
        $championship->load([
            'teams',
            'matches',
            'matches.winner:id,name'
        ]);

        $matchesByOrder = $championship->matches->keyBy('order');

        $championshipData = [
            'id' => $championship->id,
            'name' => $championship->name,
            'status' => $championship->status,
            'created_at' => $championship->created_at,
            'updated_at' => $championship->updated_at,
            'teams' => $championship->teams->map(function ($team) use ($championship) {
                $points = null;
                $standing = $team->standings()->where('championship_id', $championship->id)->first();
                if ($standing) {
                    $points = $standing->points;
                }
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'points' => $points,
                ];
            }),
            'matches' => $championship->matches,
        ];

        return response()->json([
            'championship' => $championshipData,
            'winners' => [
                'terceiro' => $matchesByOrder->get(6)?->winner,
                'segundo' => $matchesByOrder->get(7)?->winner,
                'primeiro' => $matchesByOrder->get(8)?->winner,
            ],
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChampionshipRequest $request, Championship $championship)
    {
        $championship->update($request->validated());
        return response()->json($championship, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Championship $championship)
    {
        //
    }
}
