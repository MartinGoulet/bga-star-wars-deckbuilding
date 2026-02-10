<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class ConditionalEffect extends EffectInstance {

    public function __construct(
        private array $effects,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $ctx->getGameEngine()->insertEffectsAfterCurrentEffect(
            $this->sourceCard,
            $this->effects
        );
    }

}
