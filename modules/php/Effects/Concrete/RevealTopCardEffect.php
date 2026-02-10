<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use CardInstance;

final class RevealTopCardEffect extends EffectInstance {

    public function __construct(
        private string $from,
        private string $storeAs,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $card = $this->getCardFromZone($ctx, $this->from);

        if ($card == null || $this->storeAs == '') {
            return;
        }

        $ctx->globals->set($this->storeAs, [$card->id]);

        $ctx->game->notify->all(
            'onRevealTopCard',
            clienttranslate('${player_name} reveals ${card_name} from the galaxy deck'),
            [
                'player_id' => $ctx->currentPlayer()->playerId,
                'card' => $card,
                'from' => $this->from,
            ]
        );
    }

    private function getCardFromZone(GameContext $ctx, string $zone): ?CardInstance {
        switch ($zone) {
            case ZONE_GALAXY_DECK:
                return $ctx->cardRepository->getCardOnTopOfGalaxyDeck();
            default:
                throw new \InvalidArgumentException("Unknown zone: " . $zone);
        }
    }
}
