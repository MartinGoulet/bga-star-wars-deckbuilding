<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

class PlayerTurn_ActionResolveDamageShipBase extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_ATTACK_RESOLVE_DAMAGE_SHIP_BASE,
            name: 'playerTurnActionResolveDamageShipBase',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select a ship or base to assign damage to'),
            descriptionMyTurn: clienttranslate('${you} must select a ship or base to assign damage to'),
        );
    }

    public function getArgs(): array {
        $ctx = new GameContext($this->game);
        $ships = $ctx->opponentPlayer()->getCardsInShipArea();
        return [
            'ships' => $ships,
            '_no_notify' => count($ships) < 2,
        ];
    }

    function onEnteringState() {
        $ctx = new GameContext($this->game);
        $ships = $ctx->opponentPlayer()->getCardsInShipArea();
        $remainingDamage = $this->game->globals->get(GVAR_REMAINING_DAMAGE_TO_ASSIGN, 0);

        // If only one ship, auto assign damage
        if (count($ships) === 1) {
            $ship = current($ships);
            $remainingDamage = $ctx->assignDamageToTarget($ship, $remainingDamage);
            $this->globals->set(GVAR_REMAINING_DAMAGE_TO_ASSIGN, $remainingDamage);

            // Verify if ship is destroyed
            $this->verifyShipDestroy($ship, $ctx);

            // Deal damage to base directly
            array_shift($ships);
        }

        if (count($ships) === 0) {
            $base = $this->game->cardRepository->getActiveBase($ctx->opponentPlayer()->playerId);
            if($base !== null) {
                // Deal damage to base directly
                $remainingDamage = $ctx->assignDamageToTarget($base, $remainingDamage);

                if($base->damage >= $base->health) {
                    // Base destroyed
                    $ctx->exileCard($base->id);
                    $this->game->playerScore->inc($ctx->currentPlayer()->playerId, 1);
                }

                $this->globals->set(GVAR_REMAINING_DAMAGE_TO_ASSIGN, $remainingDamage);
            }

            return PlayerTurn_ActionSelection::class;
        }

        // The player will have to choose the ship to assign damage to
    }

    #[PossibleAction]
    public function actSelectShipToDealDamage(int $cardId, int $activePlayerId, array $args) {
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }

    private function verifyShipDestroy(CardInstance $target, GameContext $ctx) {
        if ($target->damage < $target->health) return;

        $this->game->cardRepository->addCardsToPlayerDiscard([$target->id], $ctx->opponentPlayer()->playerId);
        $target = $this->game->cardRepository->getCardById($target->id);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} destroys ${card_names} in their Ship Area'),
            [
                'player_id' => $ctx->opponentPlayer()->playerId,
                'player_name' => $this->game->getPlayerNameById($ctx->currentPlayer()->playerId),
                'cards' => [$target],
                'destination' => ZONE_PLAYER_DISCARD,
            ]
        );
    }
}
