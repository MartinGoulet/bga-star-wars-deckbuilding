<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ChoiceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\DrawCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\MoveCardEffect;

final class EffectFactory
{
    public static function create(array $data): Effect
    {
        $conditions = $data['conditions'] ?? [];
        switch ($data['type']) {
            case EFFECT_CHOICE:
                return new ChoiceEffect($data['options'], $conditions);
            case EFFECT_MOVE_CARD:
                return new MoveCardEffect($data['target'], $data['destination'], $conditions);
            case EFFECT_DRAW:
                return new DrawCardEffect($data['value'], $conditions);
            // case EFFECT_GAIN_POWER:
            //     return new GainPowerEffect($abilityData['value']);
            // case EFFECT_GAIN_RESOURCE:
            //     return new GainResourceEffect($abilityData['value']);
            // case EFFECT_GAIN_FORCE:
            //     return new GainForceEffect($abilityData['value']);
            // Add other effect types here
            default:
                throw new \InvalidArgumentException("Unknown effect type: " . $data['type']);
        }
    }
}