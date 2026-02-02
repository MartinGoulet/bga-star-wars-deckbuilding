<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PowerResolver;
use Bga\Games\StarWarsDeckbuilding\Game;

class PlayerTurn_AttackDeclaration extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_ATTACK_DECLARATION,
            name: 'playerTurnAttackDeclaration',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select a target to attack (Maximum Power: ${power})'),
            descriptionMyTurn: clienttranslate('${you} must select a target to attack (Maximum Power: ${power})'),
            transitions: [],
        );
    }

    public function getArgs(): array {
        $activePlayerId = intval($this->game->getActivePlayerId());
        $ctx = new GameContext($this->game, $activePlayerId);

        $playerFaction = $ctx->currentPlayer()->getFaction();
        $totalPower = PowerResolver::getPlayerTotalPower($activePlayerId, $ctx);

        $galaxyCards = $this->game->cardRepository->getGalaxyRow();
        $galaxyCards = array_filter($galaxyCards, function ($card) use ($playerFaction) {
            if ($card->faction === FACTION_NEUTRAL) {
                return $card->type === CARD_TYPE_SHIP;
            }
            return $card->faction !== $playerFaction;
        });

        $galaxyCardsWithHealth = array_filter(
            $galaxyCards, 
            fn($card) => $card->health <= $totalPower && $card->type !== CARD_TYPE_SHIP
        );

        $ships = $ctx->opponentPlayer()->getCardsInShipArea();
        if(empty($ships)) {
            $baseOrShips = [$this->game->cardRepository->getActiveBase($ctx->getOpponentId())];
        } else {
            $baseOrShips = $ships;
        }


        /** @var CardInstance[] */
        $targets = array_merge(
            $baseOrShips,
            array_values($galaxyCardsWithHealth)
        );

        return [
            'targets' => array_values($targets),
            'power' => $totalPower,
            '_no_notify' => count($targets) <= 1,
            'playerFaction' => $playerFaction,
        ];
    }

    function onEnteringState(array $args) {
        if (count($args['targets']) === 1) {
            $target = array_shift($args['targets']);
            $this->game->globals->set(GVAR_ATTACK_TARGET_CARD_ID, $target->id);
            $this->notify->all(
                'message',
                clienttranslate('${player_name} has no valid targets to attack and automatically commits to attack ${card_name}'),
                [
                    'player_id' => $this->game->getActivePlayerId(),
                    'card_name' => $target->name,
                ]
            );
            return PlayerTurn_AttackCommit::class;
        }
    }

    #[PossibleAction]
    public function actDeclareAttack(int $cardId, int $activePlayerId, array $args) {
        $targets = $args['targets'];
        $validTargetIds = array_map(fn($card) => $card->id, $targets);
        if (!in_array($cardId, $validTargetIds)) {
            throw new \BgaUserException(clienttranslate("Invalid target selected"));
        }

        $this->game->globals->set(GVAR_ATTACK_TARGET_CARD_ID, $cardId);
        return PlayerTurn_AttackCommit::class;
    }

    #[PossibleAction]
    public function actCancel() {
        return PlayerTurn_ActionSelection::class;
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }
}
