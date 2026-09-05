<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyRules;
use CardIds;

class SoloEnemy_GainResources extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_GAIN_RESOURCES,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $message = new NotificationMessage(clienttranslate('II. Gain Resources'));
        $this->game->soloEnemyPhase->set(2, $message);

        $enemy = new SoloEnemyContext($this->game);
        $cards = array_merge(
            $enemy->getCards(ZONE_SOLO_ENEMY_PLAY),
            $enemy->getCards(ZONE_SOLO_ENEMY_SHUTTLES),
        );
        $resources = array_sum(array_map(
            fn($card) => SoloEnemyRules::getResourceValue($card),
            $cards,
        ));
        if ($enemy->getFaction() === FACTION_EMPIRE && $enemy->getActiveBase()?->health === 16) {
            $resources += count(array_filter(
                $enemy->getCards(ZONE_SOLO_ENEMY_PLAY),
                fn($card) => $card->typeArg === CardIds::STORMTROOPER,
            ));
        }

        $enemy->addResources($resources);
        return SoloEnemy_GainForce::class;
    }
}
