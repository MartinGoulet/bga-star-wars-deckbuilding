<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class MoveSelectedCardEffect extends EffectInstance {
    public function __construct(
        private string $target,
        private string $destination,
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $cardIds = $ctx->globals->get($this->cardRef) ?? [];
        if (empty($cardIds)) {
            return;
        }

        $cards = $ctx->cardRepository->getCardsByIds($cardIds);

        $player = $this->target === TARGET_SELF
            ? $ctx->currentPlayer()
            : $ctx->opponentPlayer();

        foreach ($cards as $cardToMove) {
            switch ($this->destination) {
                case ZONE_HAND:
                    $player->moveCardToHand($cardToMove);
                    break;
                case ZONE_DISCARD:
                    $player->moveCardToDiscard($cardToMove);
                    break;
                case ZONE_TOP_DECK:
                    $player->moveCardToTopOfDeck($cardToMove);
                    break;
                case ZONE_GALAXY_DISCARD:
                    $player->moveCardToGalaxyDiscard($cardToMove);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown destination for MoveCardEffect: " . $this->destination);
            }
        }
    }
}
