<?php

namespace Bga\Games\StarWarsDeckbuilding\Filters\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Filters\FilterInstance;

final class HasCardTypeFilter implements FilterInstance
{
    /** @param string[] $cardTypes */
    public function __construct(private array $cardTypes) {
    }

    public function apply(GameContext $ctx, array $cards): array {
        return array_filter($cards, fn($card) => in_array($card->type, $this->cardTypes));
    }
}