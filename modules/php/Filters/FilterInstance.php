<?php

namespace Bga\Games\StarWarsDeckbuilding\Filters;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

interface FilterInstance {

    /** 
     * @param CardInstance[] $cards
     * @return CardInstance[]  
     */
    public function apply(GameContext $ctx, array $cards): array;
    
}