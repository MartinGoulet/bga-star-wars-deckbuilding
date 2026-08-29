<?php

namespace Bga\Games\StarWarsDeckbuilding\Condition\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class HasPurchasableGalaxyDiscardCardCondition extends Condition
{
    public function isSatisfied(GameContext $ctx): bool
    {
        $player = $ctx->currentPlayer();
        $resources = $ctx->game->playerResources->get($player->playerId);
        $faction = $player->getFaction();

        foreach ($ctx->cardRepository->getGalaxyDiscardPile() as $card) {
            if ($card->cost <= $resources && in_array($card->faction, [$faction, FACTION_NEUTRAL], true)) {
                return true;
            }
        }

        return false;
    }
}