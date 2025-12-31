<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

use Bga\Games\StarWarsDeckbuilding\Effects\Effect;

final class DrawCardEffect extends Effect
{
    private int $value;

    public function __construct(int $value, array $conditions)
    {
        parent::__construct($conditions);
        $this->value = $value;
    }

    public function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void {
        $ctx->currentPlayer()->drawCards($this->value);
    }
}