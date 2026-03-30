<?php

namespace Tests\Unit;

use App\Support\MatchRules;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class MatchRulesTest extends TestCase
{
    public function test_home_team_wins_when_scores_more_goals(): void
    {
        $rules = new MatchRules();

        $winnerId = $rules->resolveWinnerId(
            teamHomeId: 1,
            teamAwayId: 2,
            goalsHome: 3,
            goalsAway: 1,
            homePoints: null,
            awayPoints: null,
            homeRegistrationDate: null,
            awayRegistrationDate: null,
        );

        $this->assertSame(1, $winnerId);
    }

    public function test_away_team_wins_when_scores_more_goals(): void
    {
        $rules = new MatchRules();

        $winnerId = $rules->resolveWinnerId(
            teamHomeId: 1,
            teamAwayId: 2,
            goalsHome: 0,
            goalsAway: 2,
            homePoints: null,
            awayPoints: null,
            homeRegistrationDate: null,
            awayRegistrationDate: null,
        );

        $this->assertSame(2, $winnerId);
    }

    public function test_draw_uses_points_as_first_tiebreaker(): void
    {
        $rules = new MatchRules();

        $winnerId = $rules->resolveWinnerId(
            teamHomeId: 1,
            teamAwayId: 2,
            goalsHome: 1,
            goalsAway: 1,
            homePoints: 5,
            awayPoints: 3,
            homeRegistrationDate: Carbon::parse('2026-01-01 10:00:00'),
            awayRegistrationDate: Carbon::parse('2026-01-01 09:00:00'),
        );

        $this->assertSame(1, $winnerId);
    }

    public function test_draw_uses_registration_date_when_points_are_equal(): void
    {
        $rules = new MatchRules();

        $winnerId = $rules->resolveWinnerId(
            teamHomeId: 1,
            teamAwayId: 2,
            goalsHome: 2,
            goalsAway: 2,
            homePoints: 0,
            awayPoints: 0,
            homeRegistrationDate: Carbon::parse('2026-01-01 08:00:00'),
            awayRegistrationDate: Carbon::parse('2026-01-01 09:00:00'),
        );

        $this->assertSame(1, $winnerId);
    }

    public function test_draw_uses_away_team_when_no_home_registration_date(): void
    {
        $rules = new MatchRules();

        $winnerId = $rules->resolveWinnerId(
            teamHomeId: 1,
            teamAwayId: 2,
            goalsHome: 4,
            goalsAway: 4,
            homePoints: 0,
            awayPoints: 0,
            homeRegistrationDate: null,
            awayRegistrationDate: Carbon::parse('2026-01-01 09:00:00'),
        );

        $this->assertSame(2, $winnerId);
    }

    public function test_next_slot_for_order_returns_expected_mapping(): void
    {
        $rules = new MatchRules();

        $this->assertSame(['next_order' => 5, 'home_away' => 'team_home_id'], $rules->nextSlotForOrder(1));
        $this->assertSame(['next_order' => 5, 'home_away' => 'team_away_id'], $rules->nextSlotForOrder(2));
        $this->assertSame(['next_order' => 6, 'home_away' => 'team_home_id'], $rules->nextSlotForOrder(3));
        $this->assertSame(['next_order' => 6, 'home_away' => 'team_away_id'], $rules->nextSlotForOrder(4));
        $this->assertSame(['next_order' => 8, 'home_away' => 'team_home_id'], $rules->nextSlotForOrder(5));
        $this->assertSame(['next_order' => 8, 'home_away' => 'team_away_id'], $rules->nextSlotForOrder(6));
    }

    public function test_next_slot_for_unknown_order_returns_null(): void
    {
        $rules = new MatchRules();

        $this->assertNull($rules->nextSlotForOrder(7));
    }
}
