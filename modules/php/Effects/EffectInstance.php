<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

abstract class EffectInstance {
    public array $definition;
    public array $conditions;
    public CardInstance $sourceCard;

    public function canResolve(GameContext $ctx): bool {

        /** @var Condition[] $conditions */
        $conditions = ConditionFactory::createConditions($this->conditions);

        foreach ($conditions as $condition) {
            if (!$condition->isSatisfied($ctx)) {
                return false;
            }
        }

        return true;
    }

    public abstract function resolve(GameContext $ctx): void;
}
