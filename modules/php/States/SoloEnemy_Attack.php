<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyRules;
use CardIds;
use CardInstance;

class SoloEnemy_Attack extends GameState
{
    public function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_SOLO_ENEMY_ATTACK,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $message = new NotificationMessage(clienttranslate('V. Attack'));
        $this->game->soloEnemyPhase->set(5, $message);
        
        $enemy = new SoloEnemyContext($this->game);
        $humanPlayerId = (int) $this->game->getActivePlayerId();
        $forceIsFullyWithEnemy = $enemy->isForceFullyWithEnemy();
        $attackers = $enemy->getCards(ZONE_SOLO_ENEMY_PLAY);
        $leader = $this->getLeaderInPlay($attackers);
        $attackBonus = $leader !== null
            && $leader->typeArg === CardIds::DARTH_VADER
            && $enemy->isForceWithEnemy() ? 4 : 0;
        if ($leader !== null && $leader->typeArg === CardIds::LUKE_SKYWALKER && $enemy->isForceWithEnemy()) {
            $this->resolveLukeAbility($humanPlayerId);
        }

        $bountyAttackers = array_values(array_filter(
            $attackers,
            fn(CardInstance $card) => $card->type !== CARD_TYPE_SHIP,
        ));
        $target = $this->globals->get(GVAR_SOLO_ENEMY_LEADER_GAINED, false)
            ? null
            : SoloEnemyRules::selectAttackableTarget(
                $this->game->cardRepository->getGalaxyRow(),
                $bountyAttackers,
                $forceIsFullyWithEnemy,
                $attackBonus,
            );
        if ($target !== null) {
            $targetAttackers = SoloEnemyRules::selectLeastExcessAttackers(
                $bountyAttackers,
                $target->health,
                $forceIsFullyWithEnemy,
                $attackBonus,
            );
            $targetPower = $this->getAttackPower($targetAttackers, $forceIsFullyWithEnemy, $attackBonus);
            if ($targetPower >= $target->health) {
                $this->defeatGalaxyTarget($enemy, $target, $targetAttackers);
                $attackers = array_filter(
                    $attackers,
                    fn(CardInstance $card) => !in_array($card->id, array_map(fn(CardInstance $attacker) => $attacker->id, $targetAttackers), true),
                );
            }
        }

        $remainingPower = $this->getAttackPower($attackers, $forceIsFullyWithEnemy, $attackBonus);
        $this->game->globals->set(GVAR_SOLO_ENEMY_ATTACK_POWER, $remainingPower);
        $this->attackHumanShipsAndBase($humanPlayerId, $remainingPower);

        return $this->globals->get(GVAR_SOLO_ENEMY_BASES_DESTROYED, 0) >= 3
            ? EndScore::class
            : SoloEnemy_EndTurn::class;
    }

    /** @param CardInstance[] $cards */
    private function getAttackPower(array $cards, bool $forceIsFullyWithEnemy, int $attackBonus = 0): int
    {
        $power = array_sum(array_map(
            fn(CardInstance $card) => SoloEnemyRules::getPowerValue($card, $forceIsFullyWithEnemy),
            $cards,
        ));
        $usesVaderBonus = $attackBonus > 0 && $this->getLeaderInPlay($cards)?->typeArg === CardIds::DARTH_VADER;
        return $power + ($usesVaderBonus ? $attackBonus : 0);
    }

    /** @param CardInstance[] $cards */
    private function getLeaderInPlay(array $cards): ?CardInstance
    {
        foreach ($cards as $card) {
            if (in_array($card->typeArg, [CardIds::DARTH_VADER, CardIds::LUKE_SKYWALKER], true)) {
                return $card;
            }
        }
        return null;
    }

    /** @param CardInstance[] $attackers */
    private function defeatGalaxyTarget(SoloEnemyContext $enemy, CardInstance $target, array $attackers): void
    {
        $this->game->cardRepository->addCardToGalaxyDiscard($target->id);
        $this->notify->all(
            'onSoloEnemyDefeatGalaxyCard',
            clienttranslate('The enemy defeats ${card_name} in the Galaxy Row'),
            ['card' => $target, 'attackers' => array_values($attackers)],
        );

        $resources = SoloEnemyRules::getRewardValue($target, EFFECT_GAIN_RESOURCE);
        if ($resources > 0) {
            $enemy->addResources($resources);
        }
        $force = SoloEnemyRules::getRewardValue($target, EFFECT_GAIN_FORCE);
        if ($force > 0) {
            $enemy->gainForce($force, $target);
        }
        $this->refillGalaxyRow();
    }

    private function attackHumanShipsAndBase(int $humanPlayerId, int $damage): void
    {
        if ($damage <= 0) {
            return;
        }

        $ctx = new GameContext($this->game);
        $ships = $this->game->cardRepository->getPlayerShips($humanPlayerId);
        usort($ships, fn(CardInstance $first, CardInstance $second) =>
            ($first->health - $first->damage) <=> ($second->health - $second->damage)
        );

        foreach ($ships as $ship) {
            if ($damage <= 0) {
                break;
            }
            $damage = $ctx->assignDamageToTarget($ship, $damage);
            if ($ship->damage >= $ship->health) {
                $this->game->cardRepository->addCardsToPlayerDiscard([$ship->id], $humanPlayerId);
                $this->notify->all(
                    'onDiscardCards',
                    clienttranslate('The enemy destroys ${card_names} in the Ship Area'),
                    [
                        'player_id' => $humanPlayerId,
                        'cards' => [$this->game->cardRepository->getCardById($ship->id)],
                        'destination' => ZONE_PLAYER_DISCARD,
                    ],
                );
            }
        }

        if ($damage <= 0) {
            return;
        }

        $base = $this->game->cardRepository->getActiveBase($humanPlayerId);
        if ($base === null) {
            return;
        }

        $ctx->assignDamageToTarget($base, $damage);
        if ($base->damage >= $base->health) {
            $this->game->cardRepository->addCardToExile($base->id);
            $destroyedBases = (int) $this->globals->get(GVAR_SOLO_ENEMY_BASES_DESTROYED, 0) + 1;
            $this->globals->set(GVAR_SOLO_ENEMY_BASES_DESTROYED, $destroyedBases);
            $this->globals->set(GVAR_SOLO_ENEMY_BASE_DESTROYED, true);
            $this->notify->all(
                'onSoloEnemyDestroyBase',
                clienttranslate('The enemy destroys ${card_name}'),
                ['card' => $base],
            );
        }
    }

    private function resolveLukeAbility(int $humanPlayerId): void
    {
        $ships = array_filter(
            $this->game->cardRepository->getPlayerShips($humanPlayerId),
            fn(CardInstance $card) => $card->type === CARD_TYPE_SHIP,
        );
        if (empty($ships)) {
            return;
        }

        usort($ships, fn(CardInstance $first, CardInstance $second) =>
            ($second->health - $second->damage) <=> ($first->health - $first->damage)
        );
        $ship = $ships[0];
        $this->game->cardRepository->addCardsToPlayerDiscard([$ship->id], $humanPlayerId);
        $this->notify->all(
            'onSoloEnemyLeaderAbility',
            clienttranslate('Luke Skywalker destroys ${card_name}'),
            ['card' => $ship],
        );
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
