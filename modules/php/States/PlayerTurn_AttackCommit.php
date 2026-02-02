<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\PowerResolver;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

class PlayerTurn_AttackCommit extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PLAYER_TURN_ATTACK_COMMIT,
            name: 'playerTurnAttackCommit',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select attackers'),
            descriptionMyTurn: clienttranslate('${you} must select attackers'),
            transitions: [],
        );
    }

    public function getArgs(): array {
        $activePlayerId = intval($this->game->getActivePlayerId());
        $target = $this->game->cardRepository->getCardById(
            $this->game->globals->get(GVAR_ATTACK_TARGET_CARD_ID)
        );

        $alreadlyAttackingIds = $this->game->globals->get(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
        $attackers = $this->game->cardRepository->getPlayerPlayArea($activePlayerId);
        $attackers = array_filter($attackers, fn($card) => $this->canCardAttack($card, $alreadlyAttackingIds));

        return [
            'target' => $target,
            'attackers' => array_values($attackers),
        ];
    }

    function onEnteringState(int $activePlayerId) {
    }

    #[PossibleAction]
    public function actCancel() {
        return PlayerTurn_ActionSelection::class;
    }

    /**
     * @param int[] $cardIds
    */
    #[PossibleAction]
    public function actCommitAttack(#[IntArrayParam] array $cardIds, int $activePlayerId, array $args) {
        $attackersIds = array_map(fn($card) => $card->id, $args['attackers']);

        // check if cardIds are all in attackersIds
        foreach($cardIds as $cardId) {
            if(!in_array($cardId, $attackersIds)) {
                throw new \BgaUserException(clienttranslate("Invalid attacker selected"));
            }
        }

        $this->game->globals->set(GVAR_ATTACKERS_CARD_IDS, $cardIds);
        return PlayerTurn_AttackResolve::class;
    }

    public function canCardAttack(CardInstance $card, array $alreadyAttackingIds): bool {
        if(in_array($card->id, $alreadyAttackingIds)) {
            return false;
        }
        if($card->type === CARD_TYPE_SHIP && $card->location === ZONE_GALAXY_ROW) {
            return false;
        }

        // If in the ship area, you can always attack ship
        if($card->type === CARD_TYPE_SHIP) {
            return true;
        }

        $resolver = new PowerResolver($this->game);
        $power = $resolver->getPowerOfCard($card);
        return $power > 0;
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }
}
