<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PurchaseResolver;
use Bga\Games\StarWarsDeckbuilding\Game;

class Purchase_Begin extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PURCHASE_BEGIN,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    function onEnteringState(int $activePlayerId) {

        $cardId = $this->globals->get(GVAR_PURCHASE_CARD_ID);
        $ctx = new GameContext($this->game);
        
         // Increment the number of purchases this round
        $this->game->nbrPurchasesThisRound->inc(1);

        $activePlayerId = $ctx->currentPlayer()->playerId;
        $card = $ctx->cardRepository->getCard($cardId);

        // Deduct the card's cost from the active player's resources
        $this->game->playerResources->inc($activePlayerId, -$card->cost);

        // Notify players
        $this->notify->all(
            'message',
            clienttranslate('${player_name} purchases ${card_name} from the Galaxy Row'),
            [
                'player_id' => $activePlayerId,
                'card' => $card,
            ]
        );

        $engine = $ctx->getGameEngine();
        $engine->setNextState(Purchase_Destination::class);
        $engine->addCardEffect($card, TRIGGER_ON_PURCHASE_BEGIN);
        return $engine->run();
    }

}
