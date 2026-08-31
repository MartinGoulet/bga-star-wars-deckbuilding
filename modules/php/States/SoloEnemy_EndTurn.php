<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use CardIds;
use CardInstance;

class SoloEnemy_EndTurn extends \Bga\GameFramework\States\GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_END_TURN,
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
        $cardsToMuster = [
            CardIds::REBEL_TROOPER,
            CardIds::STORMTROOPER,
            CardIds::TEMPLE_GUARDIAN,
            CardIds::INQUISITOR,
            CardIds::DARTH_VADER,
            CardIds::LUKE_SKYWALKER,
        ];

        foreach ($enemy->getCards(ZONE_SOLO_ENEMY_PLAY) as $card) {
            if ($card->type === CARD_TYPE_SHIP) {
                continue;
            }

            $destination = in_array($card->typeArg, $cardsToMuster, true)
                ? ZONE_SOLO_ENEMY_MUSTER_HIDDEN
                : ZONE_EXILE;
            $enemy->moveCard($card, $destination);
            $this->notify->all(
                $destination === ZONE_EXILE ? 'onSoloEnemyCardExiled' : 'onSoloEnemyCardReturnedToMuster',
                $destination === ZONE_EXILE
                    ? clienttranslate('The enemy exiles ${card_name}')
                    : clienttranslate('The enemy returns ${card_name} to Muster face down'),
                [
                    'card' => $destination === ZONE_SOLO_ENEMY_MUSTER_HIDDEN ? $card->getOnlyId() : $card,
                    'card_name' => $card->name,
                    'destination' => $destination,
                ],
            );

            if ($destination === ZONE_EXILE && $card->typeArg === CardIds::OUTER_RIM_PILOT) {
                $enemy->gainForce(1, $card);
            }
        }

        $resources = $enemy->getResources();
        $progress = (int) $this->globals->get(GVAR_SOLO_ENEMY_PROGRESS, 0);
        $advance = $resources > 0 ? $resources : 1;
        $newProgress = min(10, $progress + $advance);
        $this->globals->set(GVAR_SOLO_ENEMY_RESOURCES, 0);
        $this->globals->set(GVAR_SOLO_ENEMY_PROGRESS, $newProgress);

        if ($newProgress !== $progress) {
            $this->notify->all(
                'onSoloEnemyProgressChanged',
                clienttranslate('The enemy advances on the progress track'),
                ['progress' => $newProgress, 'amount' => $newProgress - $progress],
            );
        }

        if ($newProgress === 10 && !$this->globals->get(GVAR_SOLO_ENEMY_LEADER_GAINED, false)) {
            $leader = $enemy->getCards(ZONE_SOLO_ENEMY_LEADER)[0] ?? null;
            if ($leader !== null) {
                $leaderIsHidden = $this->globals->get(GVAR_SOLO_ENEMY_LEADER_ASSAULTED, false);
                $destination = $leaderIsHidden
                    ? ZONE_SOLO_ENEMY_MUSTER_HIDDEN
                    : ZONE_SOLO_ENEMY_MUSTER_VISIBLE;
                $enemy->moveCard($leader, $destination);
                $this->globals->set(GVAR_SOLO_ENEMY_LEADER_GAINED, true);
                $this->notify->all(
                    'onSoloEnemyLeaderGained',
                    $leaderIsHidden
                        ? clienttranslate('The enemy gains its Leader face down at Muster')
                        : clienttranslate('The enemy gains its Leader at Muster'),
                    ['card' => $leaderIsHidden ? $leader->getOnlyId() : $leader, 'destination' => $destination],
                );
            }
        }

        $this->globals->set(GVAR_SOLO_ENEMY_ATTACK_POWER, 0);
        return PlayerTurn_StartTurn::class;
    }
}
