<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ChoiceEffect;
use Bga\Games\StarWarsDeckbuilding\Game;
use BgaVisibleSystemException;

class Effect_Choice extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_EFFECT_CHOICE,
            name: 'playerTurnAskChoice',
            type: StateType::MULTIPLE_ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select a choice'),
            descriptionMyTurn: clienttranslate('${you} must select a choice'),
            transitions: [],
        );
    }

    public function getArgs(): array
    {
        $ctx = new GameContext($this->game);
        /** @var ChoiceEffect $currentEffect */
        $currentEffect = $ctx->getGameEngine()->getCurrentEffect();
        $args = $currentEffect->getArgs($ctx);
        if (count($args['options']) === 1) {
            $args['_no_notify'] = true;
        }
        return $args;
    }

    function onEnteringState(array $args, int $activePlayerId) {
        if(count($args['options']) === 1) {
            return $this->actMakeChoice(0, $activePlayerId, $args);
        }
        $this->gamestate->setPlayersMultiactive([$args['target']], '', true);
    }

    #[PossibleAction]
    public function actMakeChoice(int $choiceId, int $activePlayerId, array $args): string
    {
        // Validate choice
        $this->assertChoice($choiceId, $args['options']);

        // Init context
        $ctx = new GameContext($this->game, $activePlayerId);

        // Resolve choice
        return $ctx->getGameEngine()->resume(['choice' => $choiceId]);
    }

    function zombie(int $playerId) {
        // the code to run when the player is a Zombie
    }

    private function assertChoice(int $choiceId, array $options): void
    {
        if (!isset($options[$choiceId])) {
            throw new BgaVisibleSystemException("Invalid choice id: $choiceId");
        }
    }
}
