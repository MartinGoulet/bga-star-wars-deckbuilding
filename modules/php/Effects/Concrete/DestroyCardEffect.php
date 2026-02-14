<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use CardInstance;

final class DestroyCardEffect extends EffectInstance {

    public function __construct(
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $cardIds = $ctx->globals->get($this->cardRef);
        if (!empty($cardIds)) {
            foreach ($cardIds as $cardId) {
                $card = $ctx->cardRepository->getCard($cardId);
                if ($card) {
                    $this->destroyCard($ctx, $card);
                }
            }
        }
    }

    private function destroyCard(GameContext $ctx, CardInstance $card): void {
        // If card is in the galaxy zone, discard it to the galaxy discard pile
        if (in_array($card->location, [ZONE_GALAXY_DECK, ZONE_GALAXY_ROW])) {
            $ctx->galaxy()->destroyCard($card);
        } else if ($card->isOwnedBy($ctx->currentPlayer()->playerId)) {
            $ctx->currentPlayer()->destroyCard($card);
        } else {
            $ctx->opponentPlayer()->destroyCard($card);
        }
    }
}
