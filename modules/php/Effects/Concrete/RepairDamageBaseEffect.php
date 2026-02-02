<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class RepairDamageBaseEffect extends EffectInstance {
    public function __construct(private int $repairAmount) {
    }

    public function resolve(GameContext $ctx): void {
        $playerId = $ctx->currentPlayer()->playerId;
        $baseCard = $ctx->cardRepository->getActiveBase($playerId);
        $baseCard->damage = max(0, $baseCard->damage - $this->repairAmount);

        $damages[$baseCard->id] = $baseCard->damage;
        $ctx->globals->set(GVAR_DAMAGE_ON_CARDS, $damages);

        $ctx->game->notify->all(
            'onRepairDamageBase',
            clienttranslate('${player_name} repairs ${damage} damage(s) to ${card_name} (total damage: ${total_damage}/${health})'),
            [
                'player_id' => $playerId,
                'card' => $baseCard,
                'damage' => $this->repairAmount,
                'health' => $baseCard->health,
                'total_damage' => $baseCard->damage,
            ]
        );
    }
}
