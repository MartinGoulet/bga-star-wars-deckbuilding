<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PowerResolver;
use BgaUserException;
use CardInstance;

class PlayerTurn_AttackResolve extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_ATTACK_RESOLVE,
            type: StateType::GAME,

            transitions: [],
            updateGameProgression: false,
        );
    }

    public function getArgs(): array {
        return ['_no_notify' => true];
    }

    function onEnteringState(int $activePlayerId, array $args): string {
        $target = $this->getTarget();
        $attackers = $this->getAttakers();

        $this->keepAttackers($attackers);
        $ctx = new GameContext($this->game, $activePlayerId);

        if ($target->type === CARD_TYPE_BASE) {
            return $this->resolveAttackOnBase($ctx, $target, $attackers);
        } else  if ($target->location === ZONE_GALAXY_ROW) {
            return $this->resolveAttackOnUnit($ctx, $target, $attackers);
        } else {
            return $this->resolveAttackOnShip($ctx, $target, $attackers);
        }
    }

    /**
     * @param int $attackerPlayerId
     * @param CardInstance $target
     * @param CardInstance[] $attackers
     */
    private function resolveAttackOnBase(GameContext $ctx, CardInstance $target, array $attackers): string {
        $resolver = new PowerResolver($this->game);
        $damages = $resolver->getPowerOfCards($attackers);
        $this->globals->set(GVAR_REMAINING_DAMAGE_TO_ASSIGN, $damages);
        return PlayerTurn_ActionResolveDamageShipBase::class;
    }

    /**
     * @param int $attackerPlayerId
     * @param CardInstance $target
     * @param CardInstance[] $attackers
     */
    private function resolveAttackOnUnit(GameContext $ctx, CardInstance $target, array $attackers): string {
        $this->game->cardRepository->addCardToGalaxyDiscard($target->id);
        $this->game->notify->all(
            'onDiscardGalaxyCard',
            clienttranslate('${player_name} destroys ${card_name} in the Galaxy Row'),
            [
                'player_id' => $ctx->currentPlayer()->playerId,
                'card' => $target,
            ]
        );

        if (in_array($target->faction, [FACTION_REBEL, FACTION_EMPIRE]) && empty($target->rewards)) {
            if ($target->type !== CARD_TYPE_SHIP) {
                var_dump([
                    'target' => $target,
                    'rewards' => $target->rewards,
                ]);
                die('No rewards defined for faction unit');
            }
        }

        $ctx = new GameContext($this->game);
        $engine = $ctx->getGameEngine();

        $engine->addCardEffect($target, TRIGGER_REWARD);
        return $engine->run();
    }

    /**
     * @param int $attackerPlayerId
     * @param CardInstance $target
     * @param CardInstance[] $attackers
     */
    private function resolveAttackOnShip(GameContext $ctx, CardInstance $target, array $attackers): string {
        $resolver = new PowerResolver($this->game);
        $damages = $resolver->getPowerOfCards($attackers);
        $remaining = $ctx->assignDamageToTarget($target, $damages);

        if ($target->damage == $target->health) {
            $this->game->cardRepository->addCardsToPlayerDiscard([$target->id], $ctx->opponentPlayer()->playerId);
            $target = $this->game->cardRepository->getCardById($target->id);

            $this->game->notify->all(
                'onDiscardCards',
                clienttranslate('${player_name} destroys ${card_names} in their Ship Area'),
                [
                    'player_id' => $ctx->opponentPlayer()->playerId,
                    'cards' => [$target],
                    'destination' => ZONE_PLAYER_DISCARD,
                ]
            );
        }

        if ($remaining > 0) {
            return PlayerTurn_ActionResolveDamageShipBase::class;
        }

        return PlayerTurn_ActionSelection::class;
    }

    private function getTarget() {
        return $this->game->cardRepository->getCardById(
            $this->game->globals->get(GVAR_ATTACK_TARGET_CARD_ID)
        );
    }

    private function getAttakers() {
        return $this->game->cardRepository->getCardsByIds(
            $this->game->globals->get(GVAR_ATTACKERS_CARD_IDS, [])
        );
    }

    private function keepAttackers(array $attackers) {
        $alreadyAttackingCardsIds = $this->game->globals->get(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
        $newAttackerIds = array_map(fn($card) => $card->id, $attackers);
        $updatedAttackerIds = array_merge($alreadyAttackingCardsIds, $newAttackerIds);
        $this->game->globals->set(GVAR_ALREADY_ATTACKING_CARDS_IDS, $updatedAttackerIds);
    }
}
