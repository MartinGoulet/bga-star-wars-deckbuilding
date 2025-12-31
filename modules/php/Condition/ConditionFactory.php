<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\ForceIsWithYouCondition;

final class ConditionFactory
{
    public static function create(array $condition) : Condition
    {
        return match ($condition['type']) {
            CONDITION_FORCE_IS_WITH_YOU => new ForceIsWithYouCondition(),
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
