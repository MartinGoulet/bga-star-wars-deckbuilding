<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;

class Purchase_End extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PURCHASE_END,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    function onEnteringState(int $activePlayerId) {

        $ctx = new GameContext($this->game);

        $card = $this->game->cardRepository->getCard($this->globals->get(GVAR_PURCHASE_CARD_ID));
        if ($card->location === ZONE_GALAXY_ROW) {
            $ctx->currentPlayer()->discardCards([$card->id]);
        }

        return PlayerTurn_ActionSelection::class;
    }
    
}
