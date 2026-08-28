<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class OpponentDiscardedCardFromHandCondition extends Condition
{
    public function isSatisfied(GameContext $ctx): bool
    {
        $event = $ctx->event;

        return ($event['type'] ?? null) === TRIGGER_ON_CARD_DISCARDED
            && ($event['zone'] ?? null) === ZONE_HAND
            && (int) ($event['playerId'] ?? 0) === $ctx->opponentPlayer()->playerId;
    }
}