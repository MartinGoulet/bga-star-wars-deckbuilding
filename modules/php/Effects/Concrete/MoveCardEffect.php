<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

use Bga\Games\StarWarsDeckbuilding\Effects\Effect;

final class MoveCardEffect extends Effect
{
    private string $target;
    private string $destination;

    public function __construct(string $target, string $destination, array $conditions)
    {
        parent::__construct($conditions);
        $this->target = $target;
        $this->destination = $destination;
    }

    public function resolve(
        GameContext $ctx,
        CardInstance $source
    ): void {
        if ($this->target === TARGET_SELF) {
            $cardToMove = $source;
        } else {
            throw new \InvalidArgumentException("Unknown target for MoveCardEffect: " . $this->target);
        }

        switch($this->destination) {
            case ZONE_HAND:
                $ctx->currentPlayer()->moveCardToHand($cardToMove);
                break;
            default:
                throw new \InvalidArgumentException("Unknown destination for MoveCardEffect: " . $this->destination);
        }
    }
}