<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQuery;

final class HasCardsCondition extends Condition {
    public function __construct(
        private TargetQuery $targetQuery
    ) {
    }

    public function isSatisfied(GameContext $ctx): bool {
        $cards = $ctx->targetResolver->resolve($this->targetQuery);

        $result = count($cards) >= $this->targetQuery->min;
        
        return $result;
    }
}
