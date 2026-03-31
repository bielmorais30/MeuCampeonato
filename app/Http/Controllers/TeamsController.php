<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::all();
        return response()->json($teams, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request)
    {
        $team = Team::create($request->validated());
        return response()->json($team, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $team->load('championships');
        $championships = $team->championships->map(function ($championship) {
            return [
                'id' => $championship->id,
                'name' => $championship->name,
                'status' => $championship->status,
                'created_at' => $championship->created_at,
                'updated_at' => $championship->updated_at,
            ];
        });
        $teamData = [
            'id' => $team->id,
            'name' => $team->name,
            'created_at' => $team->created_at,
            'updated_at' => $team->updated_at,
            'championships' => $championships,
        ];
        return response()->json($teamData, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());
        return response()->json($team, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        //
    }
}
