<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability;

use Bga\Games\StarWarsDeckbuilding\Ability\Concrete\ChoiceAbility;
use Bga\Games\StarWarsDeckbuilding\Ability\Concrete\ConditionalAbility;
use Bga\Games\StarWarsDeckbuilding\Ability\Concrete\DrawCardAbility;
use Bga\Games\StarWarsDeckbuilding\Ability\Concrete\GainPowerAbility;
use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Game;

final class AbilityFactory {

    public static function create(array $abilityData): Ability {
        $type = $abilityData['type'];
        return match ($type) {
            ABILITY_GAIN_POWER => new GainPowerAbility($abilityData['amount']),
            ABILITY_DRAW_CARD => new DrawCardAbility($abilityData['count']),
            ABILITY_CHOICE => new ChoiceAbility(Game::get(), $abilityData['options']),
            // ABILITY_CONDITIONAL => new ConditionalAbility(
            //     Condition::fromArray($abilityData['condition']),
            //     self::create($abilityData['ability'])
            // ),
            default => throw new \InvalidArgumentException("Unknown ability type: $type"),
        };
    }

    // public static function gainPower(int $amount): Ability {
    //     return new GainPowerAbility($amount);
    // }

    // public static function drawCard(int $count): Ability {
    //     return new DrawCardAbility($count);
    // }

    // public static function choice(array $choices): Ability {
    //     return new ChoiceAbility($choices);
    // }

    // public static function conditional(
    //     Condition $condition,
    //     Ability $ability
    // ): Ability {
    //     return new ConditionalAbility($condition, $ability);
    // }
}
