<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQuery;

final class SelectCardEffect extends EffectInstance implements NeedsPlayerInput {

    private string $nextState = Effect_CardSelection::class;

    public function __construct(
        private TargetQuery $target,
        private string $storeAs,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        // This effect requires player input, so the actual resolution will happen in resolveWithPlayerInput

        $args = $this->getArgs($ctx);
        $cards = $args['selectableCards'] ?? [];

        // If only one card is selectable, automatically select it and skip the player input step
        if (count($cards) === 1) {
            $card = current($cards);
            $ctx->globals->set($this->storeAs, [$card->id]);
            $this->nextState = '';
            return;
        }

        // If random is true, randomly select cards and skip the player input step
        if ($this->target->selectionMode === SELECTION_MODE_RANDOM) {
            $selectedCards = array_rand($cards, min($this->target->max, count($cards)));
            if (!is_array($selectedCards)) {
                $selectedCards = [$selectedCards];
            }
            $selectedCardIds = array_map(fn($index) => $cards[intval($index)]->id, $selectedCards);
            $ctx->globals->set($this->storeAs, $selectedCardIds);
            $this->nextState = '';
            return;
        }
    }

    public function getNextState(): string {
        return $this->nextState;
    }

    public function getArgs(GameContext $ctx): array {
        
        $selectedCards = $ctx->targetResolver->resolve($this->target);
        $player_id = $this->target->selectionMode === SELECTION_MODE_PLAYER_CHOICE
            ? $ctx->currentPlayer()->playerId
            : $ctx->opponentPlayer()->playerId;

        if ($this->target->min == 0) {
            $description = clienttranslate('${player_name} may select up to ${nbr} card(s)');
            $descriptionMyTurn = clienttranslate('${you} may select up to ${nbr} card(s)');
        } else {
            $description = clienttranslate('${player_name} must select ${nbr} card(s)');
            $descriptionMyTurn = clienttranslate('${you} must select ${nbr} card(s)');
        }

        return [
            'nbr' => $this->target->max,
            'optional' => $this->target->min === 0,
            'target' => $this->target,
            'selectableCards' => array_values($selectedCards),
            'card' => $this->sourceCard,
            'player_name' => $ctx->game->getPlayerNameById($player_id),
            'player_id' => $player_id,
            'description' => $description,
            'descriptionMyTurn' => $descriptionMyTurn,
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
}
