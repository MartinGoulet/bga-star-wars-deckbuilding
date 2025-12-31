<?php

namespace Bga\Games\StarWarsDeckbuilding\Choices;

use Bga\Games\StarWarsDeckbuilding\Choices\Concrete\GainForceChoice;
use Bga\Games\StarWarsDeckbuilding\Choices\Concrete\GainPowerChoice;
use Bga\Games\StarWarsDeckbuilding\Choices\Concrete\GainResourceChoice;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class ChoiceFactory
{
    public static function create(GameContext $ctx, array $choice): Choice
    {
        return match ($choice['type']) {
            CHOICE_OPTION_GAIN_FORCE => new GainForceChoice($choice['value']),
            CHOICE_OPTION_GAIN_RESOURCE => new GainResourceChoice($choice['value']),
            CHOICE_OPTION_GAIN_POWER => new GainPowerChoice($choice['value']),

            default => throw new \InvalidArgumentException("Unknown choice type: " . $choice['type']),
        };
    }
}