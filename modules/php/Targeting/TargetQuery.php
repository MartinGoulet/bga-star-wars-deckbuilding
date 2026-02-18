<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting;

final class TargetQuery
{
    /** 
     * @param string[] $zones
     * @param CardFilterInterface[] $filters
     */
    public function __construct(
        public array $zones,
        public array $filters = [],
        public int $min = 1,
        public int $max = 1000,
        public string $selectionMode = SELECTION_MODE_PLAYER_CHOICE,
    ) {}
}
