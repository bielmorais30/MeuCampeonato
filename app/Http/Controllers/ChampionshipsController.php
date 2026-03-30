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
        $championships = Championship::with([
            'teams',
            'matches' => function ($query) {
                $query->whereIn('order', [8, 7, 6])->with('winner:id,name');
            }
        ])->get();

        $response = $championships->map(function ($championship) {
            $matchesByOrder = $championship->matches->keyBy('order');

            return [
                'championship' => $championship,
                'winners' => [
                    'terceiro' => $matchesByOrder->get(8)?->winner,
                    'segundo' => $matchesByOrder->get(7)?->winner,
                    'primeiro' => $matchesByOrder->get(6)?->winner,
                ],
            ];
        });

        return response()->json($response, 200);
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
        $matches = $championship->matches()
            ->whereIn('order', [8, 7, 6])
            ->with('winner:id,name')
            ->get()
            ->keyBy('order');

        return response()->json([
            'championship' => $championship,
            'winners' => [
                'terceiro' => $matches->get(8)?->winner,
                'segundo' => $matches->get(7)?->winner,
                'primeiro' => $matches->get(6)?->winner,
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
