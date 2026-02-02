<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PlayerContext;

final class HasUnitInPlayWithTrait implements Condition {
    /** @param string[] $traits */
    public function __construct(
        private string $target,
        private array $traits,
    ) {
    }

    public function isSatisfied(GameContext $ctx): bool {
        $cardsInPlay = $this->getPlayer($ctx)->getCardsInPlayArea();

        foreach ($cardsInPlay as $card) {
            // Check if card->traits contains any of the required traits
            foreach ($this->traits as $trait) {
                if (in_array($trait, $card->traits, true)) {
                    return true;    
                }
            }
        }

        return false;
    }

    private function getPlayer(GameContext $ctx): PlayerContext {
        return $this->target === TARGET_SELF ? $ctx->currentPlayer() : $ctx->opponentPlayer();
    }
}
