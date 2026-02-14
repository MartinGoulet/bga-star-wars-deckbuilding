<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class GalaxyContext {
    public function __construct(private GameContext $ctx) {
    }

    public function destroyCard(CardInstance $card): void {
        $playerId = $this->ctx->currentPlayer()->playerId;
        $this->ctx->cardRepository->addCardToTopOfDeck($card->id, $playerId);
        $card = $this->ctx->cardRepository->getCard($card->id);
        $this->ctx->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} destroys ${card_names}'),
            [
                'player_id' => $playerId,
                'cards' => [$card],
                'destination' => ZONE_GALAXY_DISCARD,
            ]
        );
    }
}
