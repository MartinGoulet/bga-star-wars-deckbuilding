<?php

namespace Bga\Games\StarWarsDeckbuilding\Choices\Concrete;

use Bga\Games\StarWarsDeckbuilding\Choices\Choice;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class GainForceChoice implements Choice {
    private int $amount;

    public function __construct(int $amount) {
        $this->amount = $amount;
    }

    public function apply(GameContext $ctx, CardInstance $source): void
    {
        $message = clienttranslate('${player_name} chooses to gain ${value} Force(s)');
        $ctx->currentPlayer()->gainForce($this->amount, $message);
    }
}