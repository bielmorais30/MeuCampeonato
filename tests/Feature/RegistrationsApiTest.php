<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\Registration;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class RegistrationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_team_creates_registration_and_standing(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Cadastro',
        ]);

        $team = Team::create([
            'name' => 'Furia',
        ]);

        $response = $this->postJson('/api/championships/' . $championship->id . '/register', [
            'team_id' => $team->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('registrations', [
            'championship_id' => $championship->id,
            'team_id' => $team->id,
        ]);

        $this->assertDatabaseHas('standings', [
            'championship_id' => $championship->id,
            'team_id' => $team->id,
            'points' => 0,
            'goal_scored' => 0,
            'goal_conceded' => 0,
        ]);
    }

    public function test_cannot_register_the_same_team_twice_in_same_championship(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Duplicada',
        ]);

        $team = Team::create([
            'name' => 'Aurora',
        ]);

        Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $team->id,
        ]);

        $response = $this->postJson('/api/championships/' . $championship->id . '/register', [
            'team_id' => $team->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['team_id']);
    }

    public function test_registering_eight_teams_starts_championship_and_creates_matches(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Mata-Mata',
        ]);

        $teams = collect(range(1, 8))->map(function ($index) {
            return Team::create([
                'name' => 'Time ' . $index,
            ]);
        });

        $response = $this->postJson('/api/championships/' . $championship->id . '/register-multiple', [
            'team_ids' => $teams->pluck('id')->all(),
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('championships', [
            'id' => $championship->id,
            'status' => 'running',
        ]);

        $this->assertDatabaseCount('registrations', 8);
        $this->assertDatabaseCount('standings', 8);
        $this->assertDatabaseCount('matches', 8);

        $quarterMatches = ChampionshipMatch::where('championship_id', $championship->id)
            ->where('phase', 'quarter')
            ->get();

        $this->assertCount(4, $quarterMatches);
        $this->assertEquals(4, $quarterMatches->whereNotNull('team_home_id')->count());
        $this->assertEquals(4, $quarterMatches->whereNotNull('team_away_id')->count());
    }

    public function test_quarter_final_winner_is_allocated_to_next_bracket_match(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Chaveamento',
        ]);

        $teams = collect(range(1, 8))->map(function ($index) {
            return Team::create([
                'name' => 'Clube ' . $index,
            ]);
        });

        $this->postJson('/api/championships/' . $championship->id . '/register-multiple', [
            'team_ids' => $teams->pluck('id')->all(),
        ])->assertCreated();

        $quarterMatch = ChampionshipMatch::where('championship_id', $championship->id)
            ->where('order', 1)
            ->firstOrFail();

        Process::fake([
            'python3*' => Process::result("2\n0\n"),
        ]);

        $response = $this->postJson('/api/championships/' . $championship->id . '/matches/' . $quarterMatch->id . '/play');

        $response->assertOk();

        $quarterMatch->refresh();
        $semiMatch = ChampionshipMatch::where('championship_id', $championship->id)
            ->where('order', 5)
            ->firstOrFail();

        $this->assertNotNull($quarterMatch->winner_id);
        $this->assertEquals($quarterMatch->winner_id, $semiMatch->team_home_id);
    }

    public function test_draw_uses_points_as_first_tiebreaker(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Desempate Pontos',
        ]);

        $homeTeam = Team::create(['name' => 'Mandante Pontos']);
        $awayTeam = Team::create(['name' => 'Visitante Pontos']);

        Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $homeTeam->id,
        ]);

        Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $awayTeam->id,
        ]);

        Standing::create([
            'championship_id' => $championship->id,
            'team_id' => $homeTeam->id,
            'points' => 5,
            'goal_scored' => 5,
            'goal_conceded' => 0,
        ]);

        Standing::create([
            'championship_id' => $championship->id,
            'team_id' => $awayTeam->id,
            'points' => 2,
            'goal_scored' => 2,
            'goal_conceded' => 0,
        ]);

        $match = ChampionshipMatch::create([
            'championship_id' => $championship->id,
            'phase' => 'third_place',
            'order' => 7,
            'team_home_id' => $homeTeam->id,
            'team_away_id' => $awayTeam->id,
        ]);

        Process::fake([
            'python3*' => Process::result("1\n1\n"),
        ]);

        $response = $this->postJson('/api/championships/' . $championship->id . '/matches/' . $match->id . '/play');

        $response
            ->assertOk()
            ->assertJsonPath('winner', $homeTeam->name)
            ->assertJsonPath('result', '1 X 1');

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'winner_id' => $homeTeam->id,
        ]);
    }

    public function test_draw_uses_registration_order_when_points_are_equal(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Desempate Data',
        ]);

        $homeTeam = Team::create(['name' => 'Mandante Data']);
        $awayTeam = Team::create(['name' => 'Visitante Data']);

        $homeRegistration = Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $homeTeam->id,
        ]);

        $awayRegistration = Registration::create([
            'championship_id' => $championship->id,
            'team_id' => $awayTeam->id,
        ]);

        Registration::whereKey($homeRegistration->id)->update(['created_at' => now()->subMinute()]);
        Registration::whereKey($awayRegistration->id)->update(['created_at' => now()]);

        Standing::create([
            'championship_id' => $championship->id,
            'team_id' => $homeTeam->id,
            'points' => 0,
            'goal_scored' => 0,
            'goal_conceded' => 0,
        ]);

        Standing::create([
            'championship_id' => $championship->id,
            'team_id' => $awayTeam->id,
            'points' => 0,
            'goal_scored' => 0,
            'goal_conceded' => 0,
        ]);

        $match = ChampionshipMatch::create([
            'championship_id' => $championship->id,
            'phase' => 'third_place',
            'order' => 7,
            'team_home_id' => $homeTeam->id,
            'team_away_id' => $awayTeam->id,
        ]);

        Process::fake([
            'python3*' => Process::result("3\n3\n"),
        ]);

        $response = $this->postJson('/api/championships/' . $championship->id . '/matches/' . $match->id . '/play');

        $response
            ->assertOk()
            ->assertJsonPath('winner', $homeTeam->name)
            ->assertJsonPath('result', '3 X 3');

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'winner_id' => $homeTeam->id,
        ]);
    }
}
