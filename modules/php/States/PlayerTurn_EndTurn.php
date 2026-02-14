<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;

class PlayerTurn_EndTurn extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_END_TURN,
            type: StateType::GAME,

            transitions: [],
            updateGameProgression: false,
        );
    }

    public function getArgs(): array {
        return [];
    }

    function onEnteringState(int $activePlayerId) {

        // First, discard all of your unit cards from play area
        $this->discardAllUnitsFromPlayArea($activePlayerId);

        // Next, discard any cards remaining in your hand
        $this->discardAllCardsFromHand($activePlayerId);

        // Return any resources counters in your resource pool to the supply
        $this->game->playerResources->set($activePlayerId, 0);

        // Reset the number of purchases this round
        $this->game->nbrPurchasesThisRound->set(0);

        // Finally, draw five cards from your deck.
        $ctx = new GameContext($this->game, $activePlayerId);
        $ctx->currentPlayer()->drawCards(5);

        // Give some extra time to the active player when he completed an action
        $this->game->giveExtraTime($activePlayerId);
        $this->game->activeNextPlayer();

        // Return to game action for the next player
        return PlayerTurn_StartTurnBase::class;
    }

    private function discardAllUnitsFromPlayArea(int $activePlayerId) {
        $playAreaCards = $this->game->cardRepository->getPlayerPlayArea($activePlayerId);
        $playAreaCards = array_reverse($playAreaCards); // Discard in reverse order to maintain order in discard pile
        $playAreaCardsIds = array_map(fn($card) => $card->id, $playAreaCards);
        $this->game->cardRepository->addCardsToPlayerDiscard(
            $playAreaCardsIds,
            $activePlayerId
        );

        $playAreaCards = $this->game->cardRepository->getCardsByIds($playAreaCardsIds);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} discards all cards from play area'),
            [
                'player_id' => $activePlayerId,
                'cards' => array_values($playAreaCards),
                'destination' => ZONE_PLAYER_DISCARD,
            ]
        );
    }

    private function discardAllCardsFromHand(int $activePlayerId) {
        $handCards = $this->game->cardRepository->getPlayerHand($activePlayerId);

        // If no cards in hand, nothing to do
        if (empty($handCards)) {
            return;
        }

        $handCardsIds = array_map(fn($card) => $card->id, $handCards);
        $this->game->cardRepository->addCardsToPlayerDiscard(
            $handCardsIds,
            $activePlayerId
        );

        $handCards = $this->game->cardRepository->getCardsByIds($handCardsIds);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} discards all cards from hand'),
            [
                'player_id' => $activePlayerId,
                'cards' => array_values($handCards),
                'destination' => ZONE_PLAYER_DISCARD,
            ]
        );
    }
}
