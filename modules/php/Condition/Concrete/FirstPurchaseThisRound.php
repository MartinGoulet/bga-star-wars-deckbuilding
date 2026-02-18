<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class FirstPurchaseThisRound extends Condition
{
    public function isSatisfied(GameContext $ctx): bool {
        return $ctx->game->nbrPurchasesThisRound->get() === 1;
    }
}