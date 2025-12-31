<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

interface Ability {
    public function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void;
}
