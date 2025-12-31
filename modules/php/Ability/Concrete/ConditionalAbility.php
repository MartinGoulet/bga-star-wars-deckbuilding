<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability\Concrete;

use Bga\Games\StarWarsDeckbuilding\Ability\Ability;
use Bga\Games\StarWarsDeckbuilding\Cards\CardInstance;
use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\PlayerState;

final class ConditionalAbility implements Ability
{
    public function __construct(
        private Condition $condition,
        private Ability $ability
    ) {}

    public function resolve(GameState $game, PlayerState $players, CardInstance $source): void
    {
        if ($this->condition->isSatisfied($game, $players, $source)) {
            $this->ability->resolve($game, $players, $source);
        }
    }
}
