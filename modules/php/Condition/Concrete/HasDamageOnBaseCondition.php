<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class HasDamageOnBaseCondition extends Condition
{
    public function isSatisfied(GameContext $ctx): bool {
        $playerId = $ctx->currentPlayer()->playerId;
        $activeBase = $ctx->cardRepository->getActiveBase($playerId);
        if ($activeBase === null) {
            return false;
        }
        return $activeBase->damage > 0;
    }
}