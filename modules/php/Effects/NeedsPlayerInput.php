<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

interface NeedsPlayerInput {
    public function getNextState(): string;
    
    public function getArgs(GameContext $context): array;
    public function onPlayerChoice(GameContext $context, array $choice): string;
}
