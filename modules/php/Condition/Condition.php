<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

interface Condition {
    public function isSatisfied(
        GameContext $ctx,
        CardInstance $source
    ): bool;
}
