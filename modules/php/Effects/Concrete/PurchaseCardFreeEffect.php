<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class PurchaseCardFreeEffect extends EffectInstance {
    public function __construct(
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $cardIds = $ctx->globals->get($this->cardRef);
        $cards = $ctx->cardRepository->getCardsByIds($cardIds);
        $card = array_shift($cards);

        $ctx->game->notify->all(
            'message',
            clienttranslate('${player_name} purchases ${card_name} for free'),
            [
                'player_id' => $ctx->currentPlayer()->playerId,
                'card' => $card,
            ]
        );
    }
}
