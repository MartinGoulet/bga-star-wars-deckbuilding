<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class RegisterPurchaseOptionEffect extends EffectInstance {

    public function resolve(GameContext $ctx): void {
        $overrides = $ctx->globals->get(GVAR_PURCHASE_OPTION_OVERRIDES, []);

        $overrides[] = [
            'option' => $this->definition['option'],
            'expires' => $this->definition['expires'],
            'sourceCardId' => $this->sourceCard->id,
            'effect' => $this->definition['option'],
        ];

        $ctx->globals->set(GVAR_PURCHASE_OPTION_OVERRIDES, $overrides);
    }
}
