<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class DefeatedInZoneCondition extends Condition
{
    public function __construct(private string $zone) {}

    public function isSatisfied(GameContext $ctx): bool {
        return $ctx->event['zone'] === $this->zone;
    }
}