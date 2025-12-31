<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

abstract class Effect {
    public function __construct(public array $conditions) {
    }

    public function canResolve(
        GameContext $ctx,
        CardInstance $source
    ): bool {

        /** @var Condition[] $conditions */
        $conditions = ConditionFactory::createConditions($this->conditions);

        foreach ($conditions as $condition) {
            if (!$condition->isSatisfied($ctx, $source)) {
                return false;
            }
        }

        return true;
    }

    public abstract function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void;
}
