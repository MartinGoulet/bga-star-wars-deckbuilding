<?php

namespace Bga\Games\StarWarsDeckbuilding\Triggers;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use CardInstance;

final class TriggerResolver {

    public static function resolve(
        GameContext $ctx,
        string $trigger,
        CardInstance $source,
    ): void {
        $triggers = array_filter(
            $source->abilities,
            fn($t) => $t['trigger'] === $trigger
        );

        foreach ($triggers as $trigger) {
            $triggerInstance = self::getTrigger($trigger);
            if (! $triggerInstance->canResolve($ctx, $source)) {
                continue;
            }
            $triggerInstance->resolve($ctx, $source);
        }
    }

    private static function getTrigger(array $data): Trigger {
        $effects = [];
        foreach ($data['effects'] as $effectData) {
            $effects[] = EffectFactory::create($effectData);
        }

        $conditions = [];
        if (array_key_exists('conditions', $data)) {
            foreach ($data['conditions'] as $conditionData) {
                $conditions[] = ConditionFactory::create($conditionData);
            }
        }

        return new Trigger($conditions, $effects);
    }
}
