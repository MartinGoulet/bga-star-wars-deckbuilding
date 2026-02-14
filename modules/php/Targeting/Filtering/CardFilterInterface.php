<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting\Filtering;

use CardInstance;

interface CardFilterInterface
{
    public function matches(CardInstance $card): bool;
}
