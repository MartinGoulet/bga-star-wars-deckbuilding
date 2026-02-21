<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;

class PlayerTurn_StartTurn extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PLAYER_TURN_START_TURN,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    function onEnteringState(int $activePlayerId) {
        // Reset prevent damage effects at the start of each turn
        $this->globals->set(GVAR_PREVENT_DAMAGE_PER_TURN_EFFECTS, []);
        // Reset attack modifiers
        $this->game->globals->set(GVAR_ATTACK_MODIFIER_PER_CARDS, []);

        return PlayerTurn_StartTurnBase::class;
    }

}
