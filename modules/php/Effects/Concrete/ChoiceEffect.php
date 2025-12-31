<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

use Bga\Games\StarWarsDeckbuilding\Effects\Effect;

final class ChoiceEffect extends Effect
{
    private array $options;

    public function __construct(array $options, array $conditions)
    {
        parent::__construct($conditions);
        $this->options = $options;
    }

    public function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void {
        // Keep track of choice effect
        $ctx->setGlobalVariable('choice_effect', [
            'source_card_id' => $source->id,
            'options' => $this->options,
        ]);

        // Ask choice
        $ctx->changeState(ST_PLAYER_TURN_ASK_CHOICE);
    }
}