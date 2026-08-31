<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyRules;
use CardIds;

class SoloEnemy_GainForce extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_GAIN_FORCE,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $enemy = new SoloEnemyContext($this->game);
        $force = 0;
        $specialCards = [CardIds::TEMPLE_GUARDIAN, CardIds::INQUISITOR, CardIds::LOBOT];

        foreach ($enemy->getCards(ZONE_SOLO_ENEMY_PLAY) as $card) {
            if (in_array($card->typeArg, $specialCards, true)) {
                continue;
            }
            $force += $card->force;
        }

        $enemy->gainForce($force);

        $forceIsFullyWithEnemy = $enemy->isForceFullyWithEnemy();
        $specialAttack = 0;
        $specialForce = 0;
        foreach ($enemy->getCards(ZONE_SOLO_ENEMY_PLAY) as $card) {
            if (!in_array($card->typeArg, $specialCards, true)) {
                continue;
            }

            if ($forceIsFullyWithEnemy) {
                $specialAttack += SoloEnemyRules::getPowerValue($card, true);
            } else {
                $specialForce += SoloEnemyRules::getForceValue($card);
            }
        }

        $enemy->gainForce($specialForce);
        $this->globals->set(GVAR_SOLO_ENEMY_ATTACK_POWER, $specialAttack);

        return SoloEnemy_Purchase::class;
    }
}
