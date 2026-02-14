<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class FactionsFilter implements CardFilterInterface
{
    /** @param string[] $factions */
    public function __construct(private array $factions, private bool $negate) {}

    public function matches(CardInstance $card): bool
    {
        return in_array($card->faction, $this->factions) !== $this->negate;
    }
}
