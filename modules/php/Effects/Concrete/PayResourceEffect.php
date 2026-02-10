<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class PayResourceEffect implements EffectInstance {
    public function __construct(private int $count) {
    }

    public function resolve(GameContext $ctx): void {
        $playerId = $ctx->currentPlayer()->playerId;
        $message = clienttranslate('${player_name} pays ${amount} Resource(s)');
        $notif = new NotificationMessage($message, [
            'player_id' => $playerId,
            'amount' => $this->count,
        ]);
        $ctx->game->playerResources->inc($playerId, -$this->count, $notif);
    }
}
