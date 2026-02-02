<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class GainForceEffect extends EffectInstance {

    public function __construct(private int $count) {
    }

    public function resolve(GameContext $ctx): void {
        $message = clienttranslate('${player_name} chooses to gain ${amount} Force');
        $ctx->currentPlayer()->gainForce($this->count, $message);
    }
}


