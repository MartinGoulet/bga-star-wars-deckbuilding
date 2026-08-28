<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class AnotherUniqueUnitInPlayCondition extends Condition
{
    public function __construct(private ?CardInstance $excludeCardRef = null) {}

    public function isSatisfied(GameContext $ctx): bool {
        $cardsInPlay = $ctx->currentPlayer()->getCardsInPlayArea();

        foreach ($cardsInPlay as $card) {
            if ($this->excludeCardRef !== null && $card->id === $this->excludeCardRef->id) {
                continue;
            }
            if ($card->unique && $card->isUnit()) {
                return true;
            }
        }
        return false;
    }
}