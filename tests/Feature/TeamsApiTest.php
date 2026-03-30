<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_team(): void
    {
        $response = $this->postJson('/api/teams', [
            'name' => 'Leoes FC',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Leoes FC');

        $this->assertDatabaseHas('teams', [
            'name' => 'Leoes FC',
        ]);
    }

    public function test_name_is_required_to_create_team(): void
    {
        $response = $this->postJson('/api/teams', [
            'name' => '',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_team_with_duplicate_name(): void
    {
        Team::create([
            'name' => 'Tigres',
        ]);

        $response = $this->postJson('/api/teams', [
            'name' => 'Tigres',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
