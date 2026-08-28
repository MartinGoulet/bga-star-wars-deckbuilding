<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\States\EndScore;

final class DealDamageEffect extends EffectInstance
{
    public function __construct(
        private int $amount,
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void
    {
        $cardIds = $ctx->globals->get($this->cardRef, []);

        foreach ($cardIds as $cardId) {
            $target = $ctx->cardRepository->getCardById((int) $cardId);
            $ctx->assignDamageToTarget($target, $this->amount);

            if ($target->damage < $target->health) {
                continue;
            }

            if ($target->type === CARD_TYPE_BASE) {
                $ctx->defeatBase($target);
                if ($ctx->game->playerScore->get($ctx->currentPlayer()->playerId) >= 3) {
                    $ctx->getGameEngine()->setNextState(EndScore::class);
                }
                continue;
            }

            if ($target->type !== CARD_TYPE_SHIP) {
                continue;
            }

            $owner = $target->isOwnedBy($ctx->currentPlayer()->playerId)
                ? $ctx->currentPlayer()
                : $ctx->opponentPlayer();
            $owner->destroyCard($target);
        }
    }
}