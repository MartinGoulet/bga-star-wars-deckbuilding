<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class GainAttackEffect extends EffectInstance {

    public function __construct(private int $count) {
    }

    public function resolve(GameContext $ctx): void {
        $modifiers = $ctx->globals->get(GVAR_ATTACK_MODIFIER_PER_CARDS, []);
        if (!isset($modifiers[$this->sourceCard->id])) {
            $modifiers[$this->sourceCard->id] = 0;
        }
        $modifiers[$this->sourceCard->id] += $this->count;
        $ctx->globals->set(GVAR_ATTACK_MODIFIER_PER_CARDS, $modifiers);
    }
}


