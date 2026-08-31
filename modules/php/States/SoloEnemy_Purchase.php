<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyRules;
use CardInstance;
use CardIds;

class SoloEnemy_Purchase extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_PURCHASE,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $enemy = new SoloEnemyContext($this->game);
        $targetState = $this->resolveCapitalShipPurchaseDestruction($enemy);
        if ($targetState !== null) {
            return $targetState;
        }
        $purchasedOuterRimPilots = 0;

        while (true) {
            $outerRimPilot = $this->getOuterRimPilot();
            $purchase = SoloEnemyRules::selectPurchase(
                $this->game->cardRepository->getGalaxyRow(),
                $outerRimPilot,
                $enemy->getFaction(),
                $enemy->getResources(),
                $purchasedOuterRimPilots,
            );

            if ($purchase === null) {
                if (!$this->discardGalaxyCardForOneResource($enemy)) {
                    break;
                }
                continue;
            }

            $enemy->spendResources($purchase->cost);
            $enemy->moveCard($purchase, ZONE_SOLO_ENEMY_MUSTER_HIDDEN);
            if ($purchase->typeArg === CardIds::OUTER_RIM_PILOT) {
                $purchasedOuterRimPilots++;
            }

            $this->notify->all(
                'onSoloEnemyPurchase',
                clienttranslate('The enemy purchases ${card_name} and places it face down at Muster'),
                [
                    'card' => $purchase->getOnlyId(), 
                    'card_name' => $purchase->name,
                    'destination' => ZONE_SOLO_ENEMY_MUSTER_HIDDEN
                ],
            );
            $this->refillGalaxyRow();
        }

        return SoloEnemy_Attack::class;
    }

    private function resolveCapitalShipPurchaseDestruction(SoloEnemyContext $enemy): ?string
    {
        if (!$this->globals->get(GVAR_SOLO_ENEMY_DESTROY_CAPITAL_ON_PURCHASE, false)) {
            return null;
        }

        $humanPlayerId = (int) $this->game->getActivePlayerId();
        $capitalShips = array_filter(
            $this->game->cardRepository->getPlayerShips($humanPlayerId),
            fn(CardInstance $card) => $card->type === CARD_TYPE_SHIP,
        );
        if (empty($capitalShips) || $enemy->getResources() < 4) {
            return null;
        }

        $pending = $this->globals->get(GVAR_SOLO_ENEMY_PENDING_TARGETS, null);
        if (is_array($pending) && isset($pending['selectedTargetId'])) {
            $selectedId = (int) $pending['selectedTargetId'];
            $target = array_find($capitalShips, fn(CardInstance $card) => $card->id === $selectedId);
            if ($target === null) {
                throw new \BgaVisibleSystemException('The selected solo target is no longer available.');
            }
            $this->globals->set(GVAR_SOLO_ENEMY_PENDING_TARGETS, []);
            $this->destroyCapitalShip($enemy, $target);
            return null;
        }

        $highestHealth = max(array_map(fn(CardInstance $card) => $card->health, $capitalShips));
        $highestHealthShips = array_values(array_filter(
            $capitalShips,
            fn(CardInstance $card) => $card->health === $highestHealth,
        ));
        if (count($highestHealthShips) > 1) {
            $this->globals->set(GVAR_SOLO_ENEMY_PENDING_TARGETS, [
                'targetIds' => array_map(fn(CardInstance $card) => $card->id, $highestHealthShips),
                'reason' => clienttranslate('Choose a capital ship for the enemy to destroy'),
                'nextState' => 'purchase',
            ]);
            return SoloEnemy_ChooseTarget::class;
        }

        $this->destroyCapitalShip($enemy, $highestHealthShips[0]);
        return null;
    }

    private function destroyCapitalShip(SoloEnemyContext $enemy, CardInstance $target): void
    {
        $enemy->spendResources(4);
        $this->game->cardRepository->addCardToExile($target->id);
        $this->notify->all(
            'onSoloEnemyCardExiled',
            clienttranslate('The enemy destroys ${card_name} with its base ability'),
            ['card' => $target, 'destination' => ZONE_EXILE],
        );
    }

    private function getOuterRimPilot(): ?\CardInstance
    {
        $cards = $this->game->cardRepository->getOuterRimDeck();
        return empty($cards) ? null : $cards[array_key_last($cards)];
    }

    private function discardGalaxyCardForOneResource(SoloEnemyContext $enemy): bool
    {
        if ($enemy->getResources() <= 3) {
            return false;
        }

        $cards = $this->game->cardRepository->getGalaxyRow();
        if (empty($cards)) {
            return false;
        }

        usort($cards, fn($first, $second) => $first->locationArg <=> $second->locationArg);
        $card = $cards[0];
        $enemy->spendResources(1);
        $this->game->cardRepository->addCardToGalaxyDiscard($card->id);
        $this->notify->all(
            'onSoloEnemyDiscardGalaxyCard',
            clienttranslate('The enemy discards ${card_name} from the Galaxy Row'),
            ['card' => $card, 'destination' => ZONE_GALAXY_DISCARD],
        );
        $this->refillGalaxyRow();
        return true;
    }

    private function refillGalaxyRow(): void
    {
        $positions = $this->game->cardRepository->getAvailableGalaxyRowPositions();
        if (empty($positions)) {
            return;
        }

        $cards = $this->game->cardRepository->drawCardsFromGalaxyDeck(count($positions), $positions);
        if (!empty($cards)) {
            $this->notify->all(
                'onRefillGalaxyRow',
                clienttranslate('Refilling Galaxy Row with ${num} card(s)'),
                ['num' => count($cards), 'newCards' => array_values($cards)],
            );
        }
    }
}
