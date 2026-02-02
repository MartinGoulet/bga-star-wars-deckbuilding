<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Game;

class PlayerTurn_StartTurnBase extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PLAYER_TURN_START_TURN_BASE,
            name: 'playerTurnStartTurnBase',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select a base'),
            descriptionMyTurn: clienttranslate('${you} must select a base'),
            transitions: [],
        );
    }

    public function getArgs(): array
    {
        $activePlayerId = intval($this->game->getActivePlayerId());
        $base = $this->game->cardRepository->getActiveBase($activePlayerId);
        return [
            'base' => $base,
            'selectableBases' => $this->game->cardRepository->getPlayerBaseDeck($activePlayerId),    
            '_no_notify' => $base !== null,
        ];
    }

    function onEnteringState(array $args) {
        $this->globals->set(GVAR_ATTACKERS_CARD_IDS, []);
        $this->globals->set(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
        $this->globals->set(GVAR_ABILITY_USED_CARD_IDS, []);
        
        if ($args['base'] !== null) {
            return PlayerTurn_StartTurnResources::class;
        }
    }

    #[PossibleAction]
    public function actSelectBase(int $cardId, int $activePlayerId, array $args)
    {

    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }
}
