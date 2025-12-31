<?php

namespace Bga\Games\StarWarsDeckbuilding\Triggers;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

class Trigger
{
    /**
     * @param Condition[] $conditions
     * @param Effect[] $effects
     */
    public function __construct(
        protected array $conditions,
        protected array $effects
    )
    {
    }

    public function canResolve(
        GameContext $ctx,
        CardInstance $source
    ): bool {
        foreach ($this->conditions as $condition) {
            if (!$condition->isSatisfied($ctx, $source)) {
                return false;
            }
        }

        return true;
    }

    public function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void {
        foreach ($this->effects as $effect) {
            if ($effect->canResolve($ctx, $source)) {
                $effect->resolve($ctx, $source);
            }
        }
    }
}