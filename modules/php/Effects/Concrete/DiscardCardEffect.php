<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PlayerContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;
use BgaUserException;

final class DiscardCardEffect
extends EffectInstance
implements NeedsPlayerInput {

    public function __construct(
        private string $target,
        private int $amount,
    ) {
    }

    public function resolve(GameContext $ctx): void {
    }

    public function getNextState(): string {
        return Effect_CardSelection::class;
    }

    public function onPlayerChoice(GameContext $ctx, array $data): string {
        $selectedCardIds = $data['cardIds'] ?? [];
        $this->getTargetPlayer($ctx)->discardCards($selectedCardIds);
        return '';
    }

    public function getArgs(GameContext $ctx): array {

        $player_id = $this->getTargetPlayer($ctx)->playerId;

        return [
            'nbr' => $this->amount,
            'optional' => false,
            'selectableCards' => array_values($ctx->getSelectableCards($player_id, [ZONE_HAND])),
            'card' => $this->sourceCard,
            'target' => $this->target,
            'player_name' => $ctx->game->getPlayerNameById($player_id),
            'player_id' => $player_id,
            'description' => clienttranslate('${player_name} must select ${nbr} card(s) to discard'),
            'descriptionMyTurn' => clienttranslate('${you} must select ${nbr} card(s) to discard'),
        ];

    }

    private function getTargetPlayer(GameContext $ctx): PlayerContext {
        return $this->target === TARGET_SELF
            ? $ctx->currentPlayer()
            : $ctx->opponentPlayer();
    }

}
