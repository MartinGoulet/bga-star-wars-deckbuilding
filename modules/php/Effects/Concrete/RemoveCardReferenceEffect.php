<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class RemoveCardReferenceEffect extends EffectInstance {
    public function __construct(private string $cardReference) {
    }

    public function resolve(GameContext $ctx): void {
        $ctx->globals->set($this->cardReference, []);
    }
}
