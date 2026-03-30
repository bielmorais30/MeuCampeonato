<?php

namespace App\Support;

use Carbon\CarbonInterface;

class MatchRules
{
    /**
     * Resolve o vencedor com base no placar e critérios de desempate.
     */
    public function resolveWinnerId(
        int $teamHomeId,
        int $teamAwayId,
        int $goalsHome,
        int $goalsAway,
        ?int $homePoints,
        ?int $awayPoints,
        ?CarbonInterface $homeRegistrationDate,
        ?CarbonInterface $awayRegistrationDate
    ): int {
        if ($goalsHome > $goalsAway) {
            return $teamHomeId;
        }

        if ($goalsAway > $goalsHome) {
            return $teamAwayId;
        }

        if (isset($homePoints, $awayPoints) && $homePoints !== $awayPoints) {
            return $homePoints > $awayPoints ? $teamHomeId : $teamAwayId;
        }

        if ($homeRegistrationDate && $awayRegistrationDate && $homeRegistrationDate < $awayRegistrationDate) {
            return $teamHomeId;
        }

        if ($homeRegistrationDate && !$awayRegistrationDate) {
            return $teamHomeId;
        }

        return $teamAwayId;
    }

    /**
     * Retorna o próximo slot no chaveamento.
     *
     * @return array{next_order:int,home_away:string}|null
     */
    public function nextSlotForOrder(int $order): ?array
    {
        $map = [
            1 => ['next_order' => 5, 'home_away' => 'team_home_id'],
            2 => ['next_order' => 5, 'home_away' => 'team_away_id'],
            3 => ['next_order' => 6, 'home_away' => 'team_home_id'],
            4 => ['next_order' => 6, 'home_away' => 'team_away_id'],
            5 => ['next_order' => 8, 'home_away' => 'team_home_id'],
            6 => ['next_order' => 8, 'home_away' => 'team_away_id'],
        ];

        return $map[$order] ?? null;
    }
}
