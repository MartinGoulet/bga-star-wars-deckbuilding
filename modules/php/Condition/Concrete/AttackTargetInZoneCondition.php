<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class AttackTargetInZoneCondition extends Condition
{
    public function __construct(private string $zone)
    {
    }

    public function isSatisfied(GameContext $ctx): bool
    {
        $targetId = $ctx->globals->get(GVAR_ATTACK_TARGET_CARD_ID, null);
        if ($targetId === null) {
            return false;
        }

        $target = $ctx->cardRepository->getCardById((int) $targetId);
        return $target->location === $this->zone;
    }
}