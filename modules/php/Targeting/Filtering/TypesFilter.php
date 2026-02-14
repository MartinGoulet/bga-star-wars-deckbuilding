<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class TypesFilter implements CardFilterInterface
{
    /** @param string[] $types */
    public function __construct(private array $types) {}

    public function matches(CardInstance $card): bool
    {
        return in_array($card->type, $this->types);
    }
}
