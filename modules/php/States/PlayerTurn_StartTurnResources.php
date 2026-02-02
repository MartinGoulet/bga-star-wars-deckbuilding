<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;

class PlayerTurn_StartTurnResources extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_START_TURN_RESOURCES,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array {
        return [
            '_no_notify' => true,
        ];
    }

    function onEnteringState(int $activePlayerId) {

        $ctx = new GameContext($this->game, $activePlayerId);

        // If the force is with you, you get 1 extra resource
        if ($ctx->currentPlayer()->hasForceWithYouForResourceGain()) {
            $notif = new NotificationMessage(
                clienttranslate('${player_name} feels the Force and gains 1 extra resource!'),
                [
                    'player_id' => $activePlayerId,
                ]
            );
            $this->game->playerResources->inc($activePlayerId, 1, $notif);
        }

        // Finally, gain the resources from each capital starship in your play area
        $capitalShips = $ctx->cardRepository->getPlayerShips($activePlayerId);
        foreach ($capitalShips as $ship) {
            $notif = $message = new NotificationMessage(
                clienttranslate('${player_name} gains ${resource} resource(s) from ${card_name}.'),
                [
                    'player_id' => $activePlayerId,
                    'card' => $ship,
                    'resource' => $ship->resource,
                ]
            );
            $this->game->playerResources->inc($activePlayerId, $ship->resource, $notif);
        }

        return PlayerTurn_ActionSelection::class;
    }
}
