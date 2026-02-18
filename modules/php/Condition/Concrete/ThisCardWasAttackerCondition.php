<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class ThisCardWasAttackerCondition extends Condition
{
    public function isSatisfied(GameContext $ctx): bool {
        return in_array($this->sourceCard->id, $ctx->event['attackerIds'] ?? []);
    }
}