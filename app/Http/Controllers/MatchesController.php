<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\Standing;
use App\Support\MatchRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process as FacadesProcess;

class MatchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getBrackets(Request $request, $championshipId)
    {
        $matches = ChampionshipMatch::with('teamHome:id,name', 'teamAway:id,name')->where('championship_id', $championshipId)
            ->orderBy('order')
            ->get();

        $matches = $matches->map(function ($match) {
            
            return [
                'id' => $match->id,
                'order' => $match->order,
                'phase' => $match->phase,
                'team_home' => $match->teamHome ? $match->teamHome->name : null,
                'team_away' => $match->teamAway ? $match->teamAway->name : null,
                'goals_home' => $match->goals_home,
                'goals_away' => $match->goals_away,
                'winner' => ($match->winner_id) ? ($match->teamHome->id === $match->winner_id ? $match->teamHome->name : $match->teamAway->name) : null,
            ];
        });

        return response()->json($matches, 200);
    }

    // Função para sortear resultados de uma partida específica 
    public function playSpecificMatch($championshipId, $matchId)
    {

        $match = ChampionshipMatch::with([
            'teamHome.registrations' => function ($query) use ($championshipId) {
                $query->where('championship_id', $championshipId)
                    ->select('id', 'team_id', 'championship_id', 'created_at')
                    ->orderBy('created_at', 'asc');
            },
            'teamAway.registrations' => function ($query) use ($championshipId) {
                $query->where('championship_id', $championshipId)
                    ->select('id', 'team_id', 'championship_id', 'created_at')
                    ->orderBy('created_at', 'asc');
            }
        ])->where('championship_id', $championshipId)
            ->where('id', $matchId)
            ->whereNull('winner_id')        // partida ainda não foi jogada
            ->whereNotNull('team_home_id')  // garantir que já tem time definido pra esse jogo
            ->whereNotNull('team_away_id')
            ->first();

        if (!$match) {
            return response()->json(['message' => 'Partida não encontrada ou não atende aos requisitos.'], 404);
        }

        // Simular resultado com o script python como o solicitado
        $result = FacadesProcess::run('python3 ' . base_path('resources/scripts/teste.py'))->output();
        $scores = explode("\n", trim($result));

        $goalsHome = (int) $scores[0];
        $goalsAway = (int) $scores[1];

        $homeRegistration = $match->teamHome?->registrations?->first();
        $awayRegistration = $match->teamAway?->registrations?->first();
        $homeRegistrationDate = $homeRegistration?->created_at;
        $awayRegistrationDate = $awayRegistration?->created_at;

        $homePoints = null;
        $awayPoints = null;

        Standing::where('championship_id', $championshipId)
            ->whereIn('team_id', [$match->team_home_id, $match->team_away_id])
            ->get()
            ->each(function ($standing) use ($match, &$homePoints, &$awayPoints) {
                if ($standing->team_id == $match->team_home_id) {
                    $homePoints = $standing->points;
                } else {
                    $awayPoints = $standing->points;
                }
            });

        $rules = app(MatchRules::class);

        $winnerId = $rules->resolveWinnerId(
            $match->team_home_id,
            $match->team_away_id,
            $goalsHome,
            $goalsAway,
            $homePoints,
            $awayPoints,
            $homeRegistrationDate,
            $awayRegistrationDate,
        );

        $teamWinnerName = $winnerId === $match->team_home_id
            ? $match->teamHome->name
            : $match->teamAway->name;

        // Atualizar os resultados da partida
        $match->update([
            'goals_home' => $goalsHome,
            'goals_away' => $goalsAway,
            'winner_id' => $winnerId
        ]);


        if($match->phase == "third_place") { // se for disputa de terceiro lugar ou final, não tem próxima fase, então só atualiza o resultado e retorna

            return response()->json(['message'  => 'Terceiro lugar decidido.',
                                     'winner'   => $teamWinnerName, 
                                     'result'   => $scores[0] . " X " . $scores[1]], 200);

        }else if($match->phase == "final") { 

            Championship::find($championshipId)->update(['status' => 'finished']); // finalizando campeonato
            return response()->json(['message' => 'Campeão decidido!', 
                                     'winner'  => $teamWinnerName, 
                                     'result' => $scores[0] . " X " . $scores[1]], 200);
        }



        // Alocar vencedor na próxima fase
        $nextOrder = $rules->nextSlotForOrder($match->order);

        if (!$nextOrder) {
            return response()->json(['message' => 'Partida finalizada sem próxima fase.'], 200);
        }

        ChampionshipMatch::where('championship_id', $championshipId)
            ->where('order', $nextOrder['next_order'])   // próxima fase
            ->whereNull($nextOrder['home_away'])         // garantir que a vaga que o time sera alocado ainda não foi ocupada
            ->first()
            ->update([
                $nextOrder['home_away'] => $winnerId
            ]);

        if($match->phase == "semi") {                                   // se for semifinal, o perdedor vai pra disputa de terceiro lugar
            $loserId = $match->team_home_id == $winnerId ? $match->team_away_id : $match->team_home_id;


            $thirdPlaceMatch = ChampionshipMatch::where('championship_id', $championshipId)
                ->where('order', 7)                                     // terceira fase
                ->first();
            
            if($match->order == 5){
                $thirdPlaceMatch->update([
                    'team_home_id' => $loserId
                ]);
            } else {
                $thirdPlaceMatch->update([
                    'team_away_id' => $loserId
                ]);
            }

        }
 
        // Atualizar tabela de classificação
        Standing::where('championship_id', $championshipId)
            ->whereIn('team_id', [$match->team_home_id, $match->team_away_id]) // captura os dois 
            ->get()
            ->each(function ($standing) use ($match, $goalsHome, $goalsAway) {
                if ($standing->team_id == $match->team_home_id) {

                    $standing->goal_scored += $goalsHome;
                    $standing->goal_conceded += $goalsAway;

                    $standing->points = $standing->goal_scored - $standing->goal_conceded; //saldo de gols da casa

                } else {
                    $standing->goal_scored += $goalsAway;
                    $standing->goal_conceded += $goalsHome;

                    $standing->points = $standing->goal_scored - $standing->goal_conceded; //saldo de gols do visitante
                }
                $standing->save();
            });

        return response()->json(['message' => 'Resultado da partida atualizado com sucesso', 
                                 'winner'  => $teamWinnerName, 
                                 'result'  => $scores[0] . " X " . $scores[1]], 200);
    }

    // Função para sortear resultados da próxima partida  
    public function playNextMatch($championshipId)
    {
        $nextMatch = ChampionshipMatch::where('championship_id', $championshipId)
            ->whereNull('winner_id')
            ->whereNotNull('team_home_id')
            ->whereNotNull('team_away_id')
            ->orderBy('order')
            ->first();

        if (!$nextMatch) {
            return response()->json(['message' => 'Partida não encontrada ou não atende aos requisitos.'], 404);
        }

        return $this->playSpecificMatch($championshipId, $nextMatch->id);
    }
}
