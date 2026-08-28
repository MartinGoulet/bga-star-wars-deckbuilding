<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class CardInPlayCondition extends Condition
{
    /** @param int[] $cardIds */
    public function __construct(
        private array $cardIds,
        private bool $negate = false
    ) {}

    public function isSatisfied(GameContext $ctx): bool {
        $cardsInPlay = array_merge(
            $ctx->currentPlayer()->getCardsInPlayArea(),
            $ctx->currentPlayer()->getCardsInShipArea(),
            [$ctx->currentPlayer()->getCardsInBase()]
        );

        foreach ($cardsInPlay as $card) {
            if (in_array($card->typeArg, $this->cardIds, true)) {
                return !$this->negate;
            }
        }
        return $this->negate;
    }
}