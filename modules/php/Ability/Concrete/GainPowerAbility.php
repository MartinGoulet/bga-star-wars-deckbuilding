<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability\Concrete;

use Bga\Games\StarWarsDeckbuilding\Ability\Ability;
use Bga\Games\StarWarsDeckbuilding\Cards\CardInstance;
use Bga\Games\StarWarsDeckbuilding\Core\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\PlayerState;

final class GainPowerAbility implements Ability {
    public function __construct(private int $amount) {
    }

    public function resolve(GameState $game, PlayerState $players, CardInstance $source): void {
        $game->players()->addPower(
            $players->currentPlayerId(),
            $this->amount
        );
    }
}
