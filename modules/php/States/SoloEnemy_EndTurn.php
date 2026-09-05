<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use CardIds;

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
        $message = new NotificationMessage(clienttranslate('VI. End Turn'));
        $this->game->soloEnemyPhase->set(6, $message);

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
        $newProgress = min(12, $progress + $advance);
        $this->globals->set(GVAR_SOLO_ENEMY_RESOURCES, 0);
        $this->globals->set(GVAR_SOLO_ENEMY_PROGRESS, $newProgress);

        if ($newProgress !== $progress) {
            $this->notify->all(
                'onSoloEnemyProgressChanged',
                clienttranslate('The enemy advances on the progress track'),
                ['progress' => $newProgress, 'amount' => $newProgress - $progress],
            );
        }

        $progressTrack = $this->game->solo_progress_track[$enemy->getFaction()] ?? [];
        for ($position = $progress + 1; $position <= $newProgress; $position++) {
            $reward = $progressTrack[$position] ?? null;
            if ($reward === null) {
                continue;
            }

            if ($reward === SOLO_GAIN_LEADER) {
                if ($this->globals->get(GVAR_SOLO_ENEMY_LEADER_GAINED, false)) {
                    continue;
                }

                if ($enemy->gainLeader()) {
                    $this->globals->set(GVAR_SOLO_ENEMY_LEADER_GAINED, true);
                }
                continue;
            }

            $enemy->applyProgressReward($reward);
        }

        $this->globals->set(GVAR_SOLO_ENEMY_ATTACK_POWER, 0);
        $this->game->soloEnemyPhase->set(0);
        return PlayerTurn_StartTurn::class;
    }
}
