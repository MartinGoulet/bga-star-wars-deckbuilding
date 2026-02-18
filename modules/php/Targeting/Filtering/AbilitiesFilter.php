<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class AbilitiesFilter implements CardFilterInterface
{
    /** @param string[] $abilities */
    public function __construct(private array $abilities, private bool $negate) {}

    public function matches(CardInstance $card): bool
    {
        foreach ($this->abilities as $ability) {
            if (in_array($ability, $card->abilities ?? [])) {
                return !$this->negate;
            }
        }
        return $this->negate;
    }
}
