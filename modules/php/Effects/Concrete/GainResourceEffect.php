<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class GainResourceEffect extends EffectInstance {

    public function __construct(private int $count) {
    }

    public function resolve(GameContext $ctx): void {
        $ctx->currentPlayer()->addResources($this->count);
    }
}
