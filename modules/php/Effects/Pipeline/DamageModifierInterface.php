<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Pipeline;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

interface DamageModifierInterface {
    /**
     * @return int The modified damage amount to assign to the target
     */
    public function apply(GameContext $ctx, CardInstance $target, int $amount): int;
}