<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

interface Condition {
    public function isSatisfied(GameContext $ctx): bool;
}
