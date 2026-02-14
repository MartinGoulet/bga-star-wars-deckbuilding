<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class TraitsFilter implements CardFilterInterface
{
    /** @param string[] $traits */
    public function __construct(private array $traits) {}

    public function matches(CardInstance $card): bool
    {
        return !empty(array_intersect($this->traits, $card->traits));
    }
}
