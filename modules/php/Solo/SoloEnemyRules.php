<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\Solo;

use CardIds;
use CardInstance;

final class SoloEnemyRules
{
    public static function getResourceValue(CardInstance $card): int
    {
        return match ($card->typeArg) {
            CardIds::REBEL_TRANSPORT => 1,
            CardIds::NEBULON_B_FRIGATE => 3,
            CardIds::LANDING_CRAFT => 4,
            default => $card->resource,
        };
    }

    public static function getForceValue(CardInstance $card): int
    {
        $specialValues = [
            CardIds::TEMPLE_GUARDIAN => 1,
            CardIds::INQUISITOR => 1,
            CardIds::LOBOT => 2,
        ];

        if (isset($specialValues[$card->typeArg])) {
            return $specialValues[$card->typeArg];
        }

        return $card->force;
    }

    public static function getPowerValue(CardInstance $card, bool $forceIsFullyWithEnemy): int
    {
        if (!$forceIsFullyWithEnemy) {
            return $card->power;
        }

        return match ($card->typeArg) {
            CardIds::TEMPLE_GUARDIAN, CardIds::INQUISITOR => 1,
            CardIds::LOBOT => 2,
            default => $card->power,
        };
    }

    public static function isAllowedFaction(CardInstance $card, string $enemyFaction): bool
    {
        return $card->faction === FACTION_NEUTRAL || $card->faction === $enemyFaction;
    }

    public static function getRewardValue(CardInstance $card, string $effectType): int
    {
        return array_sum(array_map(
            fn(array $reward) => ($reward['type'] ?? null) === $effectType ? (int) ($reward['amount'] ?? 0) : 0,
            $card->rewards,
        ));
    }

    /**
     * @param CardInstance[] $cards
     */
    public static function selectAttackTarget(array $cards, bool $forceIsFullyWithEnemy): ?CardInstance
    {
        $resourceTargets = array_filter(
            $cards,
            fn(CardInstance $card) => self::getRewardValue($card, EFFECT_GAIN_RESOURCE) > 0,
        );
        if (!empty($resourceTargets)) {
            return self::highestRewardClosestToDeck($resourceTargets, EFFECT_GAIN_RESOURCE);
        }

        if (!$forceIsFullyWithEnemy) {
            $forceTargets = array_filter(
                $cards,
                fn(CardInstance $card) => self::getRewardValue($card, EFFECT_GAIN_FORCE) > 0,
            );
            if (!empty($forceTargets)) {
                return self::highestRewardClosestToDeck($forceTargets, EFFECT_GAIN_FORCE);
            }
        }

        return null;
    }

    /**
     * @param CardInstance[] $cards
     * @param CardInstance[] $attackers
     */
    public static function selectAttackableTarget(
        array $cards,
        array $attackers,
        bool $forceIsFullyWithEnemy,
        int $attackBonus = 0,
    ): ?CardInstance {
        $attackableCards = array_filter(
            $cards,
            fn(CardInstance $card) => !empty(self::selectLeastExcessAttackers(
                $attackers,
                max(0, $card->health - $card->damage),
                $forceIsFullyWithEnemy,
                $attackBonus,
            )),
        );

        return self::selectAttackTarget($attackableCards, $forceIsFullyWithEnemy);
    }

    /**
     * @param CardInstance[] $galaxyCards
     */
    public static function selectPurchase(
        array $galaxyCards,
        ?CardInstance $outerRimPilot,
        string $enemyFaction,
        int $resources,
        int $purchasedOuterRimPilots,
    ): ?CardInstance {
        $eligible = array_filter(
            $galaxyCards,
            fn(CardInstance $card) => $card->cost <= $resources && self::isAllowedFaction($card, $enemyFaction),
        );

        $capitalShips = array_filter(
            $eligible,
            fn(CardInstance $card) => $card->type === CARD_TYPE_SHIP,
        );
        if (!empty($capitalShips)) {
            return self::highestCostClosestToDeck($capitalShips);
        }

        $nonPilotCards = array_filter(
            $eligible,
            fn(CardInstance $card) => $card->typeArg !== CardIds::OUTER_RIM_PILOT,
        );
        if (!empty($nonPilotCards)) {
            return self::highestCostClosestToDeck($nonPilotCards);
        }

        if ($purchasedOuterRimPilots < 2 && $outerRimPilot !== null && $outerRimPilot->cost <= $resources) {
            return $outerRimPilot;
        }

        return null;
    }

    /**
     * @param CardInstance[] $cards
     * @return CardInstance[]
     */
    public static function selectLeastExcessAttackers(
        array $cards,
        int $targetHealth,
        bool $forceIsWithEnemy,
        int $attackBonus = 0,
    ): array
    {
        $bestCards = [];
        $bestPower = PHP_INT_MAX;

        $findCombination = function (int $index, array $selected, int $power) use (&$findCombination, &$bestCards, &$bestPower, $cards, $targetHealth, $forceIsWithEnemy, $attackBonus): void {
            $usesVaderBonus = $attackBonus > 0 && array_find(
                $selected,
                fn(CardInstance $card) => $card->typeArg === CardIds::DARTH_VADER,
            ) !== null;
            $totalPower = $power + ($usesVaderBonus ? $attackBonus : 0);
            if ($totalPower >= $targetHealth) {
                if ($totalPower < $bestPower || ($totalPower === $bestPower && count($selected) < count($bestCards))) {
                    $bestCards = $selected;
                    $bestPower = $totalPower;
                }
                return;
            }

            if ($index >= count($cards) || $power >= $bestPower) {
                return;
            }

            $findCombination(
                $index + 1,
                [...$selected, $cards[$index]],
                $power + self::getPowerValue($cards[$index], $forceIsWithEnemy),
            );
            $findCombination($index + 1, $selected, $power);
        };

        $findCombination(0, [], 0);
        return $bestCards;
    }

    /**
     * @param CardInstance[] $cards
      * @return CardInstance
     */
    private static function highestCostClosestToDeck(array $cards): CardInstance
    {
        usort($cards, function (CardInstance $first, CardInstance $second): int {
            $costOrder = $second->cost <=> $first->cost;
            return $costOrder !== 0 ? $costOrder : $first->locationArg <=> $second->locationArg;
        });

        return $cards[0];
    }

    /**
     * @param CardInstance[] $cards
     */
    private static function highestRewardClosestToDeck(array $cards, string $effectType): CardInstance
    {
        usort($cards, function (CardInstance $first, CardInstance $second) use ($effectType): int {
            $rewardOrder = self::getRewardValue($second, $effectType) <=> self::getRewardValue($first, $effectType);
            return $rewardOrder !== 0 ? $rewardOrder : $first->locationArg <=> $second->locationArg;
        });

        return $cards[0];
    }
}