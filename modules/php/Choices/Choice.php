<?php

namespace Bga\Games\StarWarsDeckbuilding\Choices;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

interface Choice {
    public function apply(GameContext $ctx, CardInstance $source): void;
}
