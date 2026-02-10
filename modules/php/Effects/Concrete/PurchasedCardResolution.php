<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class PurchasedCardResolution extends EffectInstance {

    public function resolve(GameContext $ctx): void {

        // Add card to discard
        $ctx->game->cardRepository->addCardToPlayerDiscard($this->sourceCard->id, $ctx->currentPlayer()->playerId);

        // Notify players
        $ctx->game->notify->all(
            'onPurchaseGalaxyCard',
            clienttranslate('${player_name} purchases ${card_name} from the Galaxy Row'),
            [
                'player_id' => $ctx->currentPlayer()->playerId,
                'card' => $this->sourceCard,
            ]
        );

        $ctx->refillGalaxyRow();
    }
}