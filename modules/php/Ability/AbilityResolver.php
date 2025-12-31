<?php

namespace Bga\Games\StarWarsDeckbuilding\Ability;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class AbilityResolver {

    public static function resolveTriggered(
        GameContext $ctx,
        string $trigger,
        CardInstance $source,
    ): void {
        $abilities = array_filter(
            $source->abilities,
            fn($ability) => $ability->trigger === $trigger
        );

        foreach ($abilities as $ability) {
            $ability->resolve($ctx, $source);
        }
    }
}
