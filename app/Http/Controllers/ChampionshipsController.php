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
     */
    public function index()
    {
        $championships = Championship::with('teams')->get();
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
     */
    public function show(Championship $championship)
    {
        return response()->json($championship, 200);
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
