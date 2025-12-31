<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Choices\ChoiceFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;
use BgaVisibleSystemException;

class PlayerTurn_AskChoice extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PLAYER_TURN_ASK_CHOICE,
            name: 'playerTurnAskChoice',
            type: StateType::ACTIVE_PLAYER,

            description: clienttranslate('${actplayer} must select a choice'),
            descriptionMyTurn: clienttranslate('${you} must select a choice'),
            transitions: [],
        );
    }

    public function getArgs(): array
    {
        $info = $this->globals->get('choice_effect');

        $data = [
            'options' => array_map(fn($o) => $o['label'], $info['options']),
            'card' => $this->game->cardRepository->getCard($info['source_card_id']),
        ];

        return $data;
    }

    function onEnteringState(int $activePlayerId) {

    }

    #[PossibleAction]
    public function actMakeChoice(int $choiceId, int $activePlayerId, array $args)
    {
        // Validate choice
        $this->assertChoice($choiceId, $args['options']);

        // Init context
        $ctx = new GameContext($this->game, $activePlayerId);

        // Resolve choice
        $choice = $this->globals->get('choice_effect')['options'][$choiceId];
        $choiceInstance = ChoiceFactory::create($ctx, $choice);
        $choiceInstance->apply($ctx, $args['card']);

        return PlayerTurn_ActionSelection::class;
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
