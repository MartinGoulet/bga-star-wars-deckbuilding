<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability\Concrete;

use Bga\Games\StarWarsDeckbuilding\Ability\Ability;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

final class ChoiceAbility implements Ability
{
    /** @param Choice[] $choices */
    public function __construct(private Game $game, public array $choices) {}

    public function resolve(GameContext $ctx, CardInstance $source): void
    {
        $this->game->globals->set('choice_ability', [
            'source_card_id' => $source->id,
            'choices' => $this->choices,
        ]);

        // Ask choice
        $ctx->changeState(ST_PLAYER_TURN_ASK_CHOICE);
    }
}
