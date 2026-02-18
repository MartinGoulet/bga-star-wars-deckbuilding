<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PowerResolver;
use Bga\Games\StarWarsDeckbuilding\Core\PurchaseResolver;
use Bga\Games\StarWarsDeckbuilding\Game;
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
        $ctx = new GameContext($this->game, $activePlayerId);

        $playerFaction = $ctx->currentPlayer()->getFaction();

        $selectableCards = $this->game->cardRepository->getPlayerHand($activePlayerId);
        $selectableCardIds = array_map(fn($card) => $card->id, $selectableCards);

        $resources = $this->game->playerResources->get($activePlayerId);
        $galaxyCards = array_merge(
            $this->game->cardRepository->getGalaxyRow(),
            [current(array_reverse($this->game->cardRepository->getOuterRimDeck()))],
        );

        $galaxyCards = array_filter($galaxyCards, fn($card) => $card->cost <= $resources);
        $galaxyCards = array_filter($galaxyCards, function ($card) use ($playerFaction) {
            return $card->faction === $playerFaction || $card->faction === FACTION_NEUTRAL;
        });

        $galaxyCardIds = array_map(fn($card) => $card->id, $galaxyCards);

        $abilityUsedCardIds = $this->globals->get(GVAR_ABILITY_USED_CARD_IDS, []);
        $playArea = array_merge(
            $ctx->currentPlayer()->getCardsInPlayArea(),
            $ctx->currentPlayer()->getCardsInShipArea(),
        );

        $activeBase = $ctx->cardRepository->getActiveBase($activePlayerId);
        if ($activeBase !== null) {
            $playArea[] = $activeBase;
        }

        $selectableAbilityCardIds = array_filter($playArea, fn($card) => !in_array($card->id, $abilityUsedCardIds));
        $selectableAbilityCardIds = array_filter($selectableAbilityCardIds, fn($card) => $card->hasPlayableAbility($ctx));
        $selectableAbilityCardIds = array_map(fn($card) => $card->id, $selectableAbilityCardIds);

        $totalPower = PowerResolver::getPlayerTotalPower($activePlayerId, $ctx);

        $data = [
            'selectableCardIds' => array_values($selectableCardIds),
            'selectableGalaxyCardIds' => array_values($galaxyCardIds),
            'galaxyCardCosts' => array_map(fn($card) => [$card->id => $card], $galaxyCards),
            'hand' => $selectableCards,
            'canCommitAttack' => $totalPower > 0,
            'totalPower' => $totalPower,
            'selectableAbilityCardIds' => array_values($selectableAbilityCardIds),
            'playArea' => $playArea,
        ];

        return $data;
    }

    function onEnteringState(int $activePlayerId) {
    }

    #[PossibleAction]
    public function actCommitAttack() {
        return PlayerTurn_AttackDeclaration::class;
    }

    #[PossibleAction]
    public function actEndTurn() {
        return PlayerTurn_EndTurn::class;
    }

    #[PossibleAction]
    public function actPlayCard(int $cardId, int $activePlayerId, array $args) {
        // Verify that the card is in the player's hand
        $this->assertCardInPlayerHand($cardId, $activePlayerId);

        // Get the card instance
        $card = $this->game->cardRepository->getCard($cardId);

        // Play the card to the play area
        if ($card->type === CARD_TYPE_SHIP) {
            $this->playCardOnShipArea($card, $activePlayerId);
        } else {
            $this->playCardOnPlayArea($card, $activePlayerId);
        }

        // Add resources if applicable
        $ctx = new GameContext($this->game, $activePlayerId);
        $this->addResourcesForPlayedCard($ctx, $card);

        // Add force if applicable
        $this->addForceForPlayedCard($ctx, $card);

        // Resolve abilities
        $context = new GameContext($this->game);
        $engine = $context->getGameEngine();
        $engine->addCardEffect($card, TRIGGER_ON_PLAY);
        return $engine->run();
    }

    #[PossibleAction]
    public function actPurchaseGalaxyCard(int $cardId, int $activePlayerId, array $args) {
        $selectableGalaxyCardIds = $args['selectableGalaxyCardIds'];

        if (!in_array($cardId, $selectableGalaxyCardIds)) {
            throw new \BgaUserException("This card is not available for purchase.");
        }

        $ctx = new GameContext($this->game, $activePlayerId);
        $resolver = new PurchaseResolver($ctx);
        return $resolver->resolvePurchase($cardId);
    }

    #[PossibleAction]
    public function actUseCardAbility(int $cardId, int $activePlayerId, array $args) {
        $ctx = new GameContext($this->game, $activePlayerId);

        $abilityUsedCardIds = $this->globals->get(GVAR_ABILITY_USED_CARD_IDS, []);
        if (in_array($cardId, $abilityUsedCardIds)) {
            throw new \BgaUserException("This card's ability has already been used this turn.");
        }

        $card = $this->game->cardRepository->getCard($cardId);
        if (!$card->hasPlayableAbility($ctx)) {
            throw new \BgaUserException("This card does not have a playable ability.");
        }

        // Mark ability as used
        $abilityUsedCardIds[] = $cardId;
        $this->globals->set(GVAR_ABILITY_USED_CARD_IDS, $abilityUsedCardIds);

        // Notify players
        $this->game->notify->all(
            'onUseCardAbility',
            clienttranslate('${player_name} uses ability of ${card_name}'),
            [
                'player_id' => $activePlayerId,
                'card' => $card,
            ]
        );

        $engine = $ctx->getGameEngine();
        $engine->addCardEffect($card, TRIGGER_ACTIVATE_CARD);
        return $engine->run();
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

    private function playCardOnShipArea(CardInstance $card, int $activePlayerId): void {
        $this->game->cardRepository->addCardToShipArea($card->id, $activePlayerId);
        $this->game->notify->all(
            'onPlayCardToShipArea',
            clienttranslate('${player_name} plays ${card_name} to their Ship Area'),
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

    private function addForceForPlayedCard(GameContext $ctx, CardInstance $card): void {
        // Add Force if card has force value
        if ($card->force == 0) return;
        $ctx->currentPlayer()->gainForce($card->force, $card);
    }
}
