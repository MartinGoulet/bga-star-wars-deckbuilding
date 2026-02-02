<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class DrawCardEffect extends EffectInstance {
    private int $value;

    public function __construct(int $value) {
        $this->value = $value;
    }

    public function resolve(GameContext $ctx): void {
        $ctx->currentPlayer()->drawCards($this->value);
    }
}
