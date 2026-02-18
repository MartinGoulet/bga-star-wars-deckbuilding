<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class RegisterDelayedEffect extends EffectInstance {
    public function __construct(
        private string $trigger,
        private array $effects,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $playerId = $ctx->currentPlayer()->playerId;

        $registry = $ctx->globals->get(GVAR_DELAYED_EFFECTS, []);
        
        $registry[$playerId][] = [
            'trigger' => $this->trigger,
            'effects' => $this->effects,
            'sourceCardId' => $this->sourceCard->id,
        ];

        $ctx->globals->set(GVAR_DELAYED_EFFECTS, $registry);
    }
}
