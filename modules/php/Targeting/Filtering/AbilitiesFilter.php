<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class AbilitiesFilter implements CardFilterInterface {
    /** @param string[] $abilities */
    public function __construct(private array $abilities, private bool $negate) {
    }

    public function matches(CardInstance $card): bool {
        foreach ($card->abilities as $ability) {
            $type = $ability['type'] ?? '';
            if (in_array($type, $this->abilities)) {
                return !$this->negate;
            }
        }
        return $this->negate;
    }
}
