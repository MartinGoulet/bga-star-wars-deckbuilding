<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class HideCardsEffect extends EffectInstance
{
    public function __construct(
        private string $cardRef
    ) {
    }

    public function resolve(GameContext $ctx): void
    {
        $cardIds = $this->resolveCardIds($ctx);

        if (empty($cardIds)) {
            return;
        }

        $ctx->game->notify->all(
            'onHideCards',
            '',
            [
                'cardIds' => $cardIds,
            ]
        );
    }

    private function resolveCardIds(GameContext $ctx): array
    {
        $value = $ctx->globals->get($this->cardRef);

        if (empty($value)) {
            return [];
        }

        // Si stocké comme tableau
        if (is_array($value)) {
            return $value;
        }

        // Si stocké comme int
        return [$value];
    }
}
