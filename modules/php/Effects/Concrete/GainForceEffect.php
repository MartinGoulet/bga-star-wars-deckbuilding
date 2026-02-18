<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class GainForceEffect extends EffectInstance {

    public function __construct(private int $count) {
    }

    public function resolve(GameContext $ctx): void {
        $message = clienttranslate('${player_name} gains ${amount} Force with ${card_name}');
        $ctx->currentPlayer()->gainForce($this->count, $this->sourceCard, $message);
    }
}


