<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;

final class SelectCardEffect extends EffectInstance implements NeedsPlayerInput {

    private string $nextState = Effect_CardSelection::class;

    public function __construct(
        private string $target,
        private string $from,
        private int $count,
        private array $filters,
        private string $storeAs,
        private bool $random,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        // This effect requires player input, so the actual resolution will happen in resolveWithPlayerInput

        $args = $this->getArgs($ctx);
        $cards = $args['selectableCards'] ?? [];
        // If only one card is selectable, automatically select it and skip the player input step
        if (count($cards) === 1) {
            $this->onPlayerChoice($ctx, ['cardIds' => [current($cards)['id']]]);
            $this->nextState = '';
            return;
        }
        // If random is true, randomly select cards and skip the player input step
        if ($this->random) {
            $selectedCards = array_rand($cards, min($this->count, count($cards)));
            $this->onPlayerChoice($ctx, ['cardIds' => $selectedCards]);
            $this->nextState = '';
            return;
        }
    }

    public function getNextState(): string {
        return $this->nextState;
    }

    public function getArgs(GameContext $ctx): array {
        if ($this->target === TARGET_SELF) {
            $player_id = $ctx->currentPlayer()->playerId;
        } else {
            $player_id = $ctx->opponentPlayer()->playerId;
        }

        return [
            'nbr' => $this->count,
            'optional' => false,
            'selectableCards' => array_values($this->getSelectableCards($ctx, $player_id)),
            'card' => $this->sourceCard,
            'target' => $this->target,
            'player_name' => $ctx->game->getPlayerNameById($player_id),
            'player_id' => $player_id,
            'description' => clienttranslate('${player_name} must select ${nbr} card(s)'),
            'descriptionMyTurn' => clienttranslate('${you} must select ${nbr} card(s)'),
        ];
    }

    public function onPlayerChoice(GameContext $context, array $choice): string {
        $cardIds = $choice['cardIds'] ?? [];
        if (empty($cardIds)) {
            return '';
        }

        $context->globals->set($this->storeAs, $cardIds);

        return '';
    }

    private function getSelectableCards(GameContext $ctx, int $playerId): array {
        $cards = $ctx->getSelectableCards($playerId, [$this->from]);

        // Apply filters
        foreach ($this->filters as $filter) {
            if ($filter['type'] === CONDITION_HAS_TRAIT) {
                $cards = array_filter($cards, function ($card) use ($filter) {
                    // Verify if any filter['traits'] is in $card['traits']
                    return !empty(array_intersect($filter['traits'], $card->traits));
                });
            }
        }

        return $cards;
    }
}
