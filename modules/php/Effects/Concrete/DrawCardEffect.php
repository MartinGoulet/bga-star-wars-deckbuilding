<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class DrawCardEffect extends EffectInstance {

    public function __construct(
        private int $value,
        private array $overrideValue = []
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $value = $this->getValue($ctx);
        $ctx->currentPlayer()->drawCards($value);
    }

    private function getValue(GameContext $ctx): int {
        foreach ($this->overrideValue as $value => $conditionsToMeet) {
            $conditionsToMeet = ConditionFactory::createConditions($conditionsToMeet);
            $canResolve = true;
            foreach ($conditionsToMeet as $condition) {
                if (!$condition->isSatisfied($ctx)) {
                    $canResolve = false;
                    break;  
                }
            }
            if ($canResolve) {
                return $value;
            }
        }

        return $this->value;
    }
}
