<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class ForceIsWithYouCondition implements Condition
{
    public function isSatisfied(GameContext $ctx): bool {
        return $ctx->currentPlayer()->hasForceWithYou();
    }
}