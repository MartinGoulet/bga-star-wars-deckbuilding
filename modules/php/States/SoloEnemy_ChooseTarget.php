<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\StarWarsDeckbuilding\Game;

class SoloEnemy_ChooseTarget extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_CHOOSE_TARGET,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${you} must choose a target'),
            transitions: [],
        );
    }

    public function getArgs(): array
    {
        $pending = $this->getPendingTarget();
        $targets = $this->game->cardRepository->getCardsByIds($pending['targetIds']);

        return [
            'targets' => array_values($targets),
            'reason' => $pending['reason'] ?? '',
        ];
    }

    #[PossibleAction]
    public function actChooseTarget(int $cardId, int $activePlayerId, array $args): string
    {
        if ($activePlayerId !== (int) $this->game->getActivePlayerId()) {
            throw new \BgaUserException(clienttranslate('You are not the active player'));
        }

        $validTargetIds = array_map(fn($card) => $card->id, $args['targets']);
        if (!in_array($cardId, $validTargetIds, true)) {
            throw new \BgaUserException(clienttranslate('Invalid target selected'));
        }

        $pending = $this->getPendingTarget();
        if (!in_array($cardId, $pending['targetIds'], true)) {
            throw new \BgaUserException(clienttranslate('Invalid target selected'));
        }

        $pending['selectedTargetId'] = $cardId;
        $this->globals->set(GVAR_SOLO_ENEMY_PENDING_TARGETS, $pending);

        return match ($pending['nextState'] ?? '') {
            'purchase' => SoloEnemy_Purchase::class,
            'attack' => SoloEnemy_Attack::class,
            default => throw new \BgaVisibleSystemException('Invalid solo target continuation.'),
        };
    }

    public function zombie(int $playerId): string
    {
        $args = $this->getArgs();
        $target = $args['targets'][0] ?? null;
        if ($target === null) {
            throw new \BgaVisibleSystemException('No valid solo target is available for the zombie.');
        }

        return $this->actChooseTarget($target->id, $playerId, $args);
    }

    private function getPendingTarget(): array
    {
        $pending = $this->globals->get(GVAR_SOLO_ENEMY_PENDING_TARGETS, null);
        if (!is_array($pending) || empty($pending['targetIds']) || !isset($pending['nextState'])) {
            throw new \BgaVisibleSystemException('No solo target selection is pending.');
        }

        return $pending;
    }
}