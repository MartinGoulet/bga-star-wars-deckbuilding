<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class SelectCurrentCardEffect extends EffectInstance {

    public function __construct(
        private string $storeAs,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $ctx->globals->set($this->storeAs, [$this->sourceCard->id]);
    }
}