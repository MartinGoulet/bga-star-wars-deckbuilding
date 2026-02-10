<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\CardInPlayCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\FirstPurchaseThisRound;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\ForceIsWithYouCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasCardInZoneCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasCardInZoneWithTraitCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasDamageOnBaseCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasResourcesCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasUnitInPlayWithTrait;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\IsCardFactionCondition;

final class ConditionFactory {
    public static function create(array $condition): Condition {
        return match ($condition['type']) {
            CONDITION_FORCE_IS_WITH_YOU => new ForceIsWithYouCondition(),
            CONDITION_FORCE_IS_NOT_WITH_YOU => new ForceIsWithYouCondition(negate: true),
            CONDITION_CARD_IN_PLAY => new CardInPlayCondition($condition['cardIds']),
            CONDITION_HAS_UNIT_IN_PLAY_WITH_TRAIT => new HasUnitInPlayWithTrait(
                $condition['target'] ?? TARGET_SELF,
                $condition['traits'],
            ),
            CONDITION_HAS_CARD_IN_ZONE_WITH_TRAIT => new HasCardInZoneWithTraitCondition(
                $condition['target'] ?? TARGET_SELF,
                $condition['traits'],
                $condition['zone'],
            ),
            CONDITION_FIRST_PURCHASE_THIS_TURN => new FirstPurchaseThisRound(),
            CONDITION_HAS_DAMAGE_ON_BASE => new HasDamageOnBaseCondition(),
            CONDITION_HAS_CARD_IN_HAND => new HasCardInZoneCondition(
                count: 1,
                zones: [['target' => TARGET_SELF, 'zone' => ZONE_HAND]],
                filters: [],
            ),
            CONDITION_CARD_FACTION_IS => new IsCardFactionCondition(
                $condition['factions'],
                $condition['cardRef'],
            ),
            CONDITION_CARD_IS_ENEMY => new IsCardFactionCondition(
                $condition['factions'],
                $condition['cardRef'],
                negate: true,
            ),
            CONDITION_HAS_RESOURCES => new HasResourcesCondition($condition['count']),
            CONDITION_HAS_VALID_TARGET => new HasCardInZoneCondition(
                count: $condition['count'] ?? 1,
                zones: $condition['zones'],
                filters: $condition['filters'] ?? [],
            ),
            default => throw new \InvalidArgumentException("Unknown condition type: " . $condition['type']),
        };
    }

    /**
     * @param array<array> $conditions
     * @return Condition[]
     */
    public static function createConditions(array $conditions): array {
        return array_map(fn($condition) => self::create($condition), $conditions);
    }
}
