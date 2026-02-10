<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class MoveCardEffect extends EffectInstance
{
    private string $target;
    private string $destination;

    public function __construct(string $target, string $destination)
    {
        $this->target = $target;
        $this->destination = $destination;
    }

    public function resolve(GameContext $ctx): void {
        if ($this->target === TARGET_SELF) {
            $cardToMove = $this->sourceCard;
        } else {
            throw new \InvalidArgumentException("Unknown target for MoveCardEffect: " . $this->target);
        }

        switch($this->destination) {
            case ZONE_HAND:
                $ctx->currentPlayer()->moveCardToHand($cardToMove);
                break;
            case ZONE_DISCARD:
                $ctx->currentPlayer()->moveCardToDiscard($cardToMove);
                break;
            case ZONE_TOP_DECK:
                $ctx->currentPlayer()->moveCardToTopOfDeck($cardToMove);
                break;
            default:
                throw new \InvalidArgumentException("Unknown destination for MoveCardEffect: " . $this->destination);
        }
    }
}