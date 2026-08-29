<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition;

use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\AnotherUniqueUnitInPlayCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\CardInPlayCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\DefeatedInZoneCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\FirstPurchaseThisRound;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\ForceIsWithYouCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasCardsCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasCardsReferenceCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasDamageOnBaseCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasResourcesCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\HasPurchasableGalaxyDiscardCardCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\IsCardFactionCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\OpponentDiscardedCardFromHandCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\ThisCardWasAttackerCondition;
use Bga\Games\StarWarsDeckbuilding\Condition\Concrete\AttackTargetInZoneCondition;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQueryFactory;
use CardIds;
use CardInstance;

final class ConditionFactory {
    private static function create(CardInstance $cardRef, array $condition): Condition {
        $return = match ($condition['type']) {
            CONDITION_FORCE_IS_WITH_YOU => new ForceIsWithYouCondition(),
            CONDITION_FORCE_IS_NOT_WITH_YOU => new ForceIsWithYouCondition(negate: true),
            CONDITION_CARD_IN_PLAY => new CardInPlayCondition(
                $condition['cardIds'],
                $condition['negate'] ?? false
            ),
            CONDITION_FIRST_PURCHASE_THIS_TURN => new FirstPurchaseThisRound(),
            CONDITION_HAS_DAMAGE_ON_BASE => new HasDamageOnBaseCondition(),
            CONDITION_CARD_FACTION_IS => new IsCardFactionCondition(
                $condition['factions'],
                $condition['cardRef'],
                $condition['negate'] ?? false,
            ),
            CONDITION_HAS_RESOURCES => new HasResourcesCondition($condition['count']),
            CONDITION_HAS_CARDS => new HasCardsCondition(
                TargetQueryFactory::create($condition['target'])
            ),
            CONDITION_HAS_CARDS_REFERENCE => new HasCardsReferenceCondition(
                $condition['cardRef']
            ),
            CONDITION_THIS_CARD_WAS_ATTACKER => new ThisCardWasAttackerCondition(),
            CONDITION_DEFEATED_IN_ZONE => new DefeatedInZoneCondition(
                $condition['zone']
            ),
            CONDITION_OPPONENT_DISCARDED_CARD_FROM_HAND => new OpponentDiscardedCardFromHandCondition(),
            CONDITION_HAS_PURCHASABLE_GALAXY_DISCARD_CARD => new HasPurchasableGalaxyDiscardCardCondition(),
            CONDITION_ATTACK_TARGET_IN_ZONE => new AttackTargetInZoneCondition($condition['zone']),
            CONDITION_ANOTHER_UNIQUE_UNIT_IN_PLAY => new AnotherUniqueUnitInPlayCondition(
                excludeCardRef: $cardRef
            ),
            default => throw new \InvalidArgumentException("Unknown condition type: " . $condition['type']),
        };

        $return->setSourceCard($cardRef);
        return $return;
    }

    /**
     * @param array<array> $conditions
     * @return Condition[]
     */
    public static function createConditions(CardInstance $cardRef, array $conditions): array {
        return array_map(fn($condition) => self::create($cardRef, $condition), $conditions);
    }
}
