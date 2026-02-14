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
        public array $filters,
        public int $min,
        public int $max,
        public string $selectionMode,
    ) {}
}
