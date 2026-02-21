<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Pipeline;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use CardInstance;

final class PreventDamagePerTurnEffect implements DamageModifierInterface {
    public function __construct(
        private int $amount,
    ) {
    }

    public function apply(GameContext $ctx, CardInstance $target, int $damage): int {
        // Retrieve the current prevent-damage effects info from globals
        $info = $ctx->globals->get(GVAR_PREVENT_DAMAGE_PER_TURN_EFFECTS, []);

        // Build a unique key for this target and effect
        $key = $target->id;

        // Initialize the effect info if it doesn't exist yet
        if (!isset($info[$key])) {
            $info[$key] = [
                'amount' => $this->amount,
                'prevented' => 0,
            ];  
        }

        // Calculate how much damage can still be prevented
        $remaining = $info[$key]['amount'] - $info[$key]['prevented'];
        $preventedNow = min($damage, max(0, $remaining));
        $info[$key]['prevented'] += $preventedNow;

        // Save the updated info back to globals
        $ctx->globals->set(GVAR_PREVENT_DAMAGE_PER_TURN_EFFECTS, $info);

        // Return the remaining damage after prevention
        return $damage - $preventedNow;
    }
}