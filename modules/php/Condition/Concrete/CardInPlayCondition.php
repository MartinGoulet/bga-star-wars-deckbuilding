<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class CardInPlayCondition extends Condition
{
    /** @param int[] $cardIds */
    public function __construct(private array $cardIds) {}

    public function isSatisfied(GameContext $ctx): bool {
        $cardsInPlay = $ctx->currentPlayer()->getCardsInPlayArea();

        foreach ($cardsInPlay as $card) {
            if (in_array($card->typeArg, $this->cardIds, true)) {
                return true;
            }
        }
        return false;
    }
}