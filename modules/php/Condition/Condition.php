<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

abstract class Condition {
    protected CardInstance $sourceCard;
    
    public abstract function isSatisfied(GameContext $ctx): bool;
    
    public function setSourceCard(CardInstance $card): void {
        $this->sourceCard = $card;
    }
}
