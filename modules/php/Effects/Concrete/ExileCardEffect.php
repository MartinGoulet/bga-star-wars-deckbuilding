<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Core\PlayerContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;

final class ExileCardEffect extends EffectInstance implements NeedsPlayerInput {
    public function __construct(
        private string $target,
        private int $count,
        private array $zones,
    ) {
    }

    public function resolve(GameContext $ctx): void {
    }

    public function getNextState(): string {
        return Effect_CardSelection::class;
    }

    public function onPlayerChoice(GameContext $ctx, array $data): string {
        // Player pass
        if (!isset($data['cardIds'])) {
            return '';
        }

        foreach ($data['cardIds'] as $cardId) {
            $ctx->exileCard($cardId);
        }

        return '';
    }

    public function getArgs(GameContext $ctx): array {
        $player_id = $this->getTargetPlayer($ctx)->playerId;

        $player_id = $this->getTargetPlayer($ctx)->playerId;

        return [
            'nbr' => $this->count,
            'optional' => true,
            'selectableCards' => array_values($ctx->getSelectableCards($player_id, $this->zones)),
            'card' => $this->sourceCard,
            'target' => $this->target,
            'player_name' => $ctx->game->getPlayerNameById($player_id),
            'player_id' => $player_id,
            'description' => clienttranslate('${player_name} may select up to ${nbr} card(s) to exile'),
            'descriptionMyTurn' => clienttranslate('${you} may select up to ${nbr} card(s) to exile'),
        ];
    }

    private function getTargetPlayer(GameContext $ctx): PlayerContext {
        return $this->target === TARGET_SELF ? $ctx->currentPlayer() : $ctx->opponentPlayer();
    }
}
