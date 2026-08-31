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
                if ($ctx->isSolo() && $target->location === ZONE_SOLO_ENEMY_ACTIVE_BASE) {
                    $ctx->game->cardRepository->addCardToExile($target->id);
                    $ctx->globals->set(GVAR_SOLO_ENEMY_BASE_DESTROYED, true);
                    $ctx->globals->inc(GVAR_SOLO_ENEMY_BASES_DESTROYED, 1);
                    $ctx->game->notify->all(
                        'onSoloEnemyDestroyBase',
                        clienttranslate('${player_name} destroys ${card_name}'),
                        [
                            'player_id' => $ctx->currentPlayer()->playerId,
                            'card' => $target,
                        ],
                    );
                    if ($ctx->globals->get(GVAR_SOLO_ENEMY_BASES_DESTROYED, 0) >= 3) {
                        $ctx->getGameEngine()->setNextState(EndScore::class);
                    }
                } else {
                    $ctx->defeatBase($target);
                }
                if (!$ctx->isSolo() && $ctx->game->playerScore->get($ctx->currentPlayer()->playerId) >= 3) {
                    $ctx->getGameEngine()->setNextState(EndScore::class);
                }
                continue;
            }

            if ($target->type !== CARD_TYPE_SHIP) {
                continue;
            }

            if ($ctx->isSolo() && $target->location === ZONE_SOLO_ENEMY_PLAY) {
                $ctx->game->cardRepository->addCardToExile($target->id);
                $ctx->game->notify->all(
                    'onSoloEnemyCardExiled',
                    clienttranslate('The enemy exiles ${card_name}'),
                    ['card' => $target],
                );
                continue;
            }

            $owner = $target->isOwnedBy($ctx->currentPlayer()->playerId)
                ? $ctx->currentPlayer()
                : $ctx->opponentPlayer();
            $owner->destroyCard($target);
        }
    }
}