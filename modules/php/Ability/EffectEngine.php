<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability;

use Bga\Games\StarWarsDeckbuilding\Cards\CardInstance;

final class EffectEngine {
    public function resolveValue(array $effect, GameContext $ctx): int {
        if (isset($effect['conditional_override'])) {
            if ($this->conditionsMet(
                $effect['conditional_override']['condition'],
                $ctx
            )) {
                return $effect['conditional_override']['value'];
            }
        }

        return $effect['value'];
    }

    public function conditionsMet(array $conditions, GameContext $ctx): bool {
        foreach ($conditions as $condition) {
            $conditionChecker = ConditionFactory::getConditionChecker(
                $condition['type']
            );
            if (
                !$conditionChecker->isConditionMet($condition, $ctx)
            ) {
                return false;
            }
        }
        return true;
    }

    public function getEffectivePower(CardInstance $unit): int {
        $power = $unit->getBasePower();

        foreach ($this->getActiveStaticAbilities() as $ability) {
            if (
                $this->conditionsMet($ability, $ctx) &&
                $this->effectAppliesTo($ability, $unit)
            ) {
                $power += $ability->effects[0]['value'];
            }
        }

        return $power;
    }
}
