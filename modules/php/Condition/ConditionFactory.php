<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\CardInPlayCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\ForceIsWithYouCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasUnitInPlayWithTrait;

final class ConditionFactory
{
    public static function create(array $condition) : Condition
    {
        return match ($condition['type']) {
            CONDITION_FORCE_IS_WITH_YOU => new ForceIsWithYouCondition(),
            CONDITION_CARD_IN_PLAY => new CardInPlayCondition($condition['cardIds']),
            CONDITION_HAS_UNIT_IN_PLAY_WITH_TRAIT => new HasUnitInPlayWithTrait(
                $condition['target'] ?? TARGET_SELF,
                $condition['traits'],
            ),
            default => throw new \InvalidArgumentException("Unknown condition type: " . $condition['type']),
        };
    }

    /**
     * @param array<array> $conditions
     * @return Condition[]
     */
    public static function createConditions(array $conditions) : array
    {
        return array_map(fn($condition) => self::create($condition), $conditions);
    }

}
