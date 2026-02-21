<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

final class UniqueFilter implements CardFilterInterface {
    public function matches(CardInstance $card): bool {
        return $card->unique;
    }
}
