<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;

class Effect_CardSelection extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_EFFECT_CARD_SELECTION,
            name: 'effectCardSelection',
            type: StateType::MULTIPLE_ACTIVE_PLAYER,
        );
    }

    public function getArgs(): array {
        $ctx = new GameContext($this->game);
        /** @var EffectInstance&NeedsPlayerInput */
        $effect = $ctx->getGameEngine()->getCurrentEffect();
        return $effect->getArgs($ctx);
    }

    function onEnteringState(array $args) {
        $this->gamestate->setPlayersMultiactive([$args['player_id']], '', true);
    }

    /**
     * @param int[] $cardIds
     */
    #[PossibleAction]
    public function actCardSelection(#[IntArrayParam()] array $cardIds, array $args) {

        /** @var CardInstance[] */
        $selectableCards = $args['selectableCards'];
        $selectableCardsIds = array_map(fn($card) => $card->id, $selectableCards);

        if($args['optional']) {
            if (count($cardIds) > $args['nbr']) {
                throw new \BgaUserException(
                    sprintf(
                        'You can select up to %d cards, you selected %d',
                        $args['nbr'],
                        count($cardIds)
                    )
                );
            }
        } else {
            // Validate number of selected cards
            if (count($cardIds) !== $args['nbr']) {
                throw new \BgaUserException(
                    sprintf(
                        'You must select %d cards, you selected %d',
                        $args['nbr'],
                        count($cardIds)
                    )
                );
            }
        }


        // Validate selected cards
        foreach ($cardIds as $cardId) {
            if (!in_array($cardId, $selectableCardsIds)) {
                throw new \BgaUserException(
                    sprintf(
                        'Card with id %d is not selectable',
                        $cardId
                    )
                );
            }
        }

        $ctx = new GameContext($this->game);
        $engine = $ctx->getGameEngine();
        return $engine->resume((['cardIds' => $cardIds]));
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }
}
