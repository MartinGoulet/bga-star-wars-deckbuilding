<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class RevealCardsEffect extends EffectInstance {

    public function __construct(
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void {

        $cards = $ctx->globals->get($this->cardRef) ?? [];
        $cards = $ctx->game->cardRepository->getCardsByIds($cards);
        
        if (empty($cards)) {
            return;
        }

        foreach ($cards as $card) {
            $ctx->game->notify->all(
                'onRevealTopCard',
                clienttranslate('${player_name} reveals ${card_name}'),
                [
                    'player_id' => $ctx->currentPlayer()->playerId,
                    'card' => $card,
                    'from' => ZONE_GALAXY_DECK,
                ]
            );
        }
    }

}
