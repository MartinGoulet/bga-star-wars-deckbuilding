<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class ForceIsWithYouCondition implements Condition
{
    public function isSatisfied(
        GameContext $ctx,
        CardInstance $source
    ): bool {
        return $ctx->currentPlayer()->isForceWithYou();
    }
}