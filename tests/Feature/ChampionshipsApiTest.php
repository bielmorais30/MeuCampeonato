<?php

namespace Tests\Feature;

use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_championship(): void
    {
        $response = $this->postJson('/api/championships', [
            'name' => 'Copa Backend',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Copa Backend');

        $this->assertDatabaseHas('championships', [
            'name' => 'Copa Backend',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_championship_with_duplicate_name(): void
    {
        Championship::create([
            'name' => 'Duplicado',
        ]);

        $response = $this->postJson('/api/championships', [
            'name' => 'Duplicado',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_expected_payload_with_winners_keys(): void
    {
        $championship = Championship::create([
            'name' => 'Copa Teste',
        ]);

        $response = $this->getJson('/api/championships/' . $championship->id);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'championship' => ['id', 'name', 'status'],
                'winners' => ['terceiro', 'segundo', 'primeiro'],
            ]);
    }
}
