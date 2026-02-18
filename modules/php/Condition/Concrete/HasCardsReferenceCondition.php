<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class HasCardsReferenceCondition extends Condition {
    public function __construct(
        private string $cardReference,
    ) {
    }

    public function isSatisfied(GameContext $ctx): bool {
        $cardRef = $ctx->globals->get($this->cardReference, []);

        return !empty($cardRef);
    }
}
