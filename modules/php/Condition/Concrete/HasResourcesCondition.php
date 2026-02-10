<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class HasResourcesCondition implements Condition
{
    public function __construct(private int $count) {
    }

    public function isSatisfied(GameContext $ctx): bool {
        $playerId = $ctx->currentPlayer()->playerId;
        $resources = $ctx->game->playerResources->get($playerId);
        return $resources >= $this->count;
    }
}