<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class IsCardFactionCondition implements Condition
{
    public function __construct(
        private array $factions,
        private string $cardRef,
        private bool $negate = false,
    ) {}

    public function isSatisfied(GameContext $ctx): bool {
        /** @var array<int> */
        $cardIds = $ctx->globals->get($this->cardRef);
        if (empty($cardIds)) {
            return false;
        }
        $cardId = array_shift($cardIds);
        $card = $ctx->cardRepository->getCardById($cardId);
        $value = in_array($card->faction, $this->factions) !== $this->negate;
        return $value;
    }
}