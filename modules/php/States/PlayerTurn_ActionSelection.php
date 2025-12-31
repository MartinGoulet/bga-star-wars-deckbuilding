<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Ability\AbilityResolver;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Triggers\TriggerResolver;
use CardInstance;

class PlayerTurn_ActionSelection extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_ACTION_SELECTION,
            name: 'playerTurnActionSelection',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select an action or pass'),
            descriptionMyTurn: clienttranslate('${you} must select an action or pass'),
            transitions: [],
        );
    }

    public function getArgs(int $activePlayerId): array {
        $sql = "SELECT player_faction FROM player WHERE player_id = $activePlayerId";
        $playerFaction = $this->game->getUniqueValueFromDb($sql);

        $selectableCards = $this->game->cardRepository->getPlayerHand($activePlayerId);
        $selectableCardIds = array_map(fn($card) => $card->id, $selectableCards);

        $resources = $this->game->playerResources->get($activePlayerId);
        $galaxyCards = $this->game->cardRepository->getGalaxyRow();

        $galaxyCards = array_filter($galaxyCards, fn($card) => $card->cost <= $resources);
        $galaxyCards = array_filter($galaxyCards, function ($card) use ($playerFaction) {
            return $card->faction === $playerFaction || $card->faction === FACTION_NEUTRAL;
        });


        $galaxyCardIds = array_map(fn($card) => $card->id, $galaxyCards);


        $data = [
            'selectableCardIds' => array_values($selectableCardIds),
            'selectableGalaxyCardIds' => array_values($galaxyCardIds),
            'galaxyCardCosts' => array_map(fn($card) => [$card->id => $card], $galaxyCards),
            'hand' => $selectableCards,
        ];

        return $data;
    }

    function onEnteringState(int $activePlayerId) {
    }

    #[PossibleAction]
    public function actPlayCard(int $cardId, int $activePlayerId, array $args) {
        // Verify that the card is in the player's hand
        $this->assertCardInPlayerHand($cardId, $activePlayerId);

        // Get the card instance
        $card = $this->game->cardRepository->getCard($cardId);

        // Play the card to the play area
        $this->playCardOnPlayArea($card, $activePlayerId);

        // Add resources if applicable
        $ctx = new GameContext($this->game, $activePlayerId);
        $this->addResourcesForPlayedCard($ctx, $card);

        // Resolve abilities
        TriggerResolver::resolve($ctx, TRIGGER_ON_PLAY, $card);

        if (!$ctx->hasChangeState) {
            return PlayerTurn_ActionSelection::class;
        }
    }

    #[PossibleAction] 
    public function actPurchaseGalaxyCard(int $cardId, int $activePlayerId) {
        $resources = $this->game->playerResources->get($activePlayerId);

        $galaxyCards = $this->game->cardRepository->getGalaxyRow();
        $galaxyCards = array_filter($galaxyCards, fn($card) => $card->cost <= $resources);
        $selectableGalaxyCardIds = array_map(fn($card) => $card->id, $galaxyCards);

        if (!in_array($cardId, $selectableGalaxyCardIds)) {
            throw new \BgaUserException("This card is not available for purchase.");
        }

        $card = $this->game->cardRepository->getCard($cardId);

        // Deduct resources
        $this->game->playerResources->inc($activePlayerId, -$card->cost);

        // Add card to discard
        $this->game->cardRepository->addCardToPlayerDiscard($cardId, $activePlayerId);

        // Notify players
        $this->game->notify->all(
            'onPurchaseGalaxyCard',
            clienttranslate('${player_name} purchases ${card_name} from the Galaxy Row'),
            [
                'player_id' => $activePlayerId,
                'card' => $card,
            ]
        );

        // Resolve abilities
        $ctx = new GameContext($this->game, $activePlayerId);
        TriggerResolver::resolve($ctx, TRIGGER_WHEN_PURCHASED, $card);

        if (!$ctx->hasChangeState) {
            return PlayerTurn_ActionSelection::class;
        }
    }

    #[PossibleAction]
    public function actPass() {
        // return PlayerTurn_ActionSelection::class;
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }

    private function assertCardInPlayerHand(int $cardId, int $activePlayerId) {
        $hand = $this->game->cardRepository->getPlayerHand($activePlayerId);
        $cardIds = array_map(fn($card) => $card->id, $hand);
        if (!in_array($cardId, $cardIds)) {
            throw new \BgaUserException("This card is not in your hand.");
        }
    }

    private function playCardOnPlayArea(CardInstance $card, int $activePlayerId): void {
        $this->game->cardRepository->addCardToPlayArea($card->id, $activePlayerId);
        $this->game->notify->all(
            'onPlayCard',
            clienttranslate('${player_name} plays ${card_name}'),
            [
                'player_id' => $activePlayerId,
                'card' => $card,
            ]
        );
    }

    private function addResourcesForPlayedCard(GameContext $ctx, CardInstance $card): void {
        // Add Resources if card has resource value
        if ($card->resource == 0) return;
        $ctx->currentPlayer()->addResources($card->resource);
    }
}
