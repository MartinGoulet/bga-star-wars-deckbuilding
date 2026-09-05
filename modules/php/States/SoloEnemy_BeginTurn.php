<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use CardIds;
use CardInstance;

class SoloEnemy_BeginTurn extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_BEGIN_TURN,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $message = new NotificationMessage(clienttranslate('I. Begin Turn'));
        $this->game->soloEnemyPhase->set(1, $message);
        
        $enemy = new SoloEnemyContext($this->game);

        $pendingHumanDiscard = false;

        if ($this->globals->get(GVAR_SOLO_ENEMY_BASE_DESTROYED, false)) {
            $pendingHumanDiscard = $this->revealNextBase($enemy);
            $this->globals->set(GVAR_SOLO_ENEMY_BASE_DESTROYED, false);
        }

        foreach ($enemy->getCards(ZONE_SOLO_ENEMY_MUSTER_VISIBLE) as $card) {
            $enemy->moveCard($card, ZONE_SOLO_ENEMY_PLAY);
            $this->notify->all(
                'onSoloEnemyCardMoved',
                clienttranslate('The enemy deploys ${card_name} from Muster'),
                ['card' => $card, 'destination' => ZONE_SOLO_ENEMY_PLAY],
            );
        }

        foreach ($enemy->getCards(ZONE_SOLO_ENEMY_MUSTER_HIDDEN) as $card) {
            $enemy->moveCard($card, ZONE_SOLO_ENEMY_MUSTER_VISIBLE);
            $this->notify->all(
                'onSoloEnemyCardRevealed',
                clienttranslate('The enemy reveals a card at Muster: ${card_name}'),
                ['card' => $card, 'destination' => ZONE_SOLO_ENEMY_MUSTER_VISIBLE],
            );
        }

        if ($enemy->isForceFullyWithEnemy()) {
            $enemy->addResources(1);
        }

        if ($pendingHumanDiscard) {
            $engine = (new \Bga\Games\StarWarsDeckbuilding\Core\GameContext($this->game))->getGameEngine();
            $engine->setNextState(SoloEnemy_GainResources::class);
            return $engine->run();
        }

        return SoloEnemy_GainResources::class;
    }

    private function revealNextBase(SoloEnemyContext $enemy): bool
    {
        $bases = $enemy->getCards(ZONE_SOLO_ENEMY_BASES);
        if (empty($bases)) {
            return false;
        }

        $base = array_shift($bases);
        $enemy->moveCard($base, ZONE_SOLO_ENEMY_ACTIVE_BASE);
        $this->globals->set(GVAR_SOLO_ENEMY_BASE_DAMAGE_PREVENTION, -1);
        $this->globals->set(GVAR_SOLO_ENEMY_DESTROY_CAPITAL_ON_PURCHASE, false);
        $this->notify->all(
            'onSoloEnemyBaseRevealed',
            clienttranslate('The enemy reveals ${card_name} as its new base'),
            ['card' => $base],
        );

        $destroyedBases = (int) $this->globals->get(GVAR_SOLO_ENEMY_BASES_DESTROYED, 0);
        $isFinalBase = $destroyedBases >= 2;
        if (!$isFinalBase) {
            return match ($base->health) {
                10, 12 => $this->gainShuttleAndMoveGalaxyCard($enemy),
                14 => $this->resolveFirstBaseFourteen($enemy, $base),
                16 => $this->resolveFirstBaseSixteen($enemy, $base),
                default => false,
            };
        }

        return match ($base->health) {
            10, 12 => $this->gainShuttleAndMoveGalaxyCard($enemy),
            14 => $this->resolveFinalBaseFourteen($enemy, $base),
            16 => $this->resolveFinalBaseSixteen($enemy, $base),
            default => false,
        };
    }

    private function gainShuttleAndMoveGalaxyCard(SoloEnemyContext $enemy): bool
    {
        $enemy->gainShuttle();
        $this->moveHighestOpposingGalaxyCardToMuster($enemy);
        return false;
    }

    private function resolveFirstBaseFourteen(SoloEnemyContext $enemy, CardInstance $base): bool
    {
        $enemy->gainShuttle();
        return $enemy->isForceFullyWithEnemy()
            ? $this->queueHumanDiscard($base, false)
            : $this->gainForceAndContinue($enemy);
    }

    private function resolveFirstBaseSixteen(SoloEnemyContext $enemy, CardInstance $base): bool
    {
        if ($enemy->getFaction() === FACTION_EMPIRE) {
            return $this->enableCapitalShipPurchaseDestruction($enemy);
        }

        $enemy->gainShuttle();
        return $this->queueHumanDiscard($base, $enemy->isForceWithEnemy());
    }

    private function resolveFinalBaseFourteen(SoloEnemyContext $enemy, CardInstance $base): bool
    {
        $enemy->gainShuttle();
        if ($enemy->getFaction() === FACTION_EMPIRE) {
            if ($enemy->isForceFullyWithEnemy()) {
                return $this->discardOpposingGalaxyCards($enemy, false);
            }
            $enemy->gainForce(4);
            return false;
        }

        return $enemy->isForceFullyWithEnemy()
            ? $this->queueHumanDiscard($base, false)
            : $this->gainForceAndContinue($enemy);
    }

    private function resolveFinalBaseSixteen(SoloEnemyContext $enemy, CardInstance $base): bool
    {
        $enemy->gainShuttle();
        if ($enemy->getFaction() === FACTION_EMPIRE) {
            return false;
        }

        return $this->queueHumanDiscard($base, $enemy->isForceWithEnemy());
    }

    private function gainForceAndContinue(SoloEnemyContext $enemy): bool
    {
        $enemy->gainForce(4);
        return false;
    }

    private function enableCapitalShipPurchaseDestruction(SoloEnemyContext $enemy): bool
    {
        $this->globals->set(GVAR_SOLO_ENEMY_DESTROY_CAPITAL_ON_PURCHASE, true);
        return false;
    }

    private function enableBaseDamagePrevention(): bool
    {
        $this->globals->set(GVAR_SOLO_ENEMY_BASE_DAMAGE_PREVENTION, 2);
        return false;
    }

    private function discardOpposingGalaxyCards(SoloEnemyContext $enemy, bool $dealDamage): bool
    {
        $cards = array_values(array_filter(
            $this->game->cardRepository->getGalaxyRow(),
            fn(CardInstance $card) => $card->faction !== FACTION_NEUTRAL
                && $card->faction !== $enemy->getFaction(),
        ));
        foreach ($cards as $card) {
            $this->game->cardRepository->addCardToGalaxyDiscard($card->id);
            $this->notify->all(
                'onSoloEnemyDiscardGalaxyCard',
                clienttranslate('The enemy discards ${card_name} from the Galaxy Row'),
                ['card' => $card, 'destination' => ZONE_GALAXY_DISCARD],
            );
        }
        if ($dealDamage && !empty($cards)) {
            $this->damageHumanBase(count($cards));
        }
        $this->refillGalaxyRow();
        return false;
    }

    private function queueHumanDiscard(CardInstance $source, bool $random): bool
    {
        $humanPlayerId = (int) $this->game->getActivePlayerId();
        if (empty($this->game->cardRepository->getPlayerHand($humanPlayerId))) {
            return false;
        }

        $storeAs = 'solo_base_discard_' . $source->id;
        $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
        $effects[] = [
            'type' => EFFECT_SELECT_CARDS,
            'sourceCardId' => $source->id,
            'target' => [
                'zones' => [TARGET_SCOPE_SELF_HAND],
                'min' => 1,
                'max' => 1,
                'selectionMode' => $random ? SELECTION_MODE_RANDOM : SELECTION_MODE_PLAYER_CHOICE,
            ],
            'storeAs' => $storeAs,
        ];
        $effects[] = [
            'type' => EFFECT_MOVE_SELECTED_CARDS,
            'sourceCardId' => $source->id,
            'target' => TARGET_SELF,
            'destination' => ZONE_PLAYER_DISCARD,
            'cardRef' => $storeAs,
        ];
        $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
        return true;
    }

    private function moveHighestOpposingGalaxyCardToMuster(SoloEnemyContext $enemy): bool
    {
        $cards = array_filter(
            $this->game->cardRepository->getGalaxyRow(),
            fn($card) => $card->faction === FACTION_NEUTRAL || $card->faction === $enemy->getFaction(),
        );
        if (empty($cards)) {
            return false;
        }

        usort($cards, fn($first, $second) =>
            ($second->cost <=> $first->cost) ?: ($first->locationArg <=> $second->locationArg)
        );
        $card = $cards[0];
        $enemy->moveCard($card, ZONE_SOLO_ENEMY_MUSTER_VISIBLE);
        $this->notify->all(
            'onSoloEnemyCardMoved',
            clienttranslate('The enemy moves ${card_name} from the Galaxy Row to Muster'),
            ['card' => $card, 'destination' => ZONE_SOLO_ENEMY_MUSTER_VISIBLE],
        );
        $positions = $this->game->cardRepository->getAvailableGalaxyRowPositions();
        if (!empty($positions)) {
            $newCards = $this->game->cardRepository->drawCardsFromGalaxyDeck(count($positions), $positions);
            if (!empty($newCards)) {
                $this->notify->all(
                    'onRefillGalaxyRow',
                    clienttranslate('Refilling Galaxy Row with ${num} card(s)'),
                    ['num' => count($newCards), 'newCards' => array_values($newCards)],
                );
            }
        }
        return false;
    }

    private function damageHumanBase(int $damage): void
    {
        $humanPlayerId = (int) $this->game->getActivePlayerId();
        $base = $this->game->cardRepository->getActiveBase($humanPlayerId);
        if ($base === null) {
            return;
        }

        $ctx = new \Bga\Games\StarWarsDeckbuilding\Core\GameContext($this->game);
        $ctx->assignDamageToTarget($base, $damage);
        $this->notify->all(
            'onSoloEnemyDamageBase',
            clienttranslate('The enemy deals ${amount} damage to the human base'),
            ['amount' => $damage, 'card' => $base],
        );
    }

    private function refillGalaxyRow(): void
    {
        $positions = $this->game->cardRepository->getAvailableGalaxyRowPositions();
        if (empty($positions)) {
            return;
        }

        $newCards = $this->game->cardRepository->drawCardsFromGalaxyDeck(count($positions), $positions);
        if (!empty($newCards)) {
            $this->notify->all(
                'onRefillGalaxyRow',
                clienttranslate('Refilling Galaxy Row with ${num} card(s)'),
                ['num' => count($newCards), 'newCards' => array_values($newCards)],
            );
        }
    }
}
