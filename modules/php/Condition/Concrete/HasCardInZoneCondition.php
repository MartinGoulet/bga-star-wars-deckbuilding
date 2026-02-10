<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class HasCardInZoneCondition implements Condition {
    public function __construct(
        private int $count,
        private array $zones,
        private array $filters,
    ) {
    }

    public function isSatisfied(GameContext $ctx): bool {
        $cards = $ctx->getSelectableCardsV2($this->zones, $this->filters);
        return count($cards) >= $this->count;
    }
}
