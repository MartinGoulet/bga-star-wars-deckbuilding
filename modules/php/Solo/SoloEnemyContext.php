<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\Solo;

use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Cards\CardRepository;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardIds;
use CardInstance;

final class SoloEnemyContext
{
    public function __construct(private Game $game)
    {
    }

    public function getFaction(): string
    {
        return (string) $this->game->globals->get(GVAR_SOLO_ENEMY_FACTION);
    }

    public function getResources(): int
    {
        return (int) $this->game->globals->get(GVAR_SOLO_ENEMY_RESOURCES, 0);
    }

    public function addResources(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->game->globals->set(GVAR_SOLO_ENEMY_RESOURCES, $this->getResources() + $amount);
        $this->game->notify->all(
            'onSoloEnemyResourcesChanged',
            clienttranslate('The enemy gains ${amount} ${resource_icon}'),
            ['amount' => $amount, 'value' => $this->getResources()],
        );
    }

    public function spendResources(int $amount): void
    {
        if ($amount < 0 || $amount > $this->getResources()) {
            throw new \InvalidArgumentException('The enemy cannot spend this many resources.');
        }

        $this->game->globals->set(GVAR_SOLO_ENEMY_RESOURCES, $this->getResources() - $amount);
        $this->game->notify->all(
            'onSoloEnemyResourcesChanged',
            clienttranslate('The enemy spends ${amount} ${resource_icon}'),
            ['amount' => $amount, 'value' => $this->getResources()],
        );
    }

    public function isForceWithEnemy(): bool
    {
        $force = $this->game->forceTrack->get();
        return $this->getFaction() === FACTION_EMPIRE ? $force < 0 : $force > 0;
    }

    public function isForceFullyWithEnemy(): bool
    {
        $force = $this->game->forceTrack->get();
        return $this->getFaction() === FACTION_EMPIRE ? $force === -3 : $force === 3;
    }

    public function gainForce(int $amount, ?CardInstance $source = null): void
    {
        if ($amount <= 0) {
            return;
        }

        $direction = $this->getFaction() === FACTION_EMPIRE ? -1 : 1;
        $oldValue = $this->game->forceTrack->get();
        $newValue = max(-3, min(3, $oldValue + ($direction * $amount)));
        if ($newValue === $oldValue) {
            return;
        }

        $message = $source === null
            ? clienttranslate('The enemy gains ${amount} ${power_icon}')
            : clienttranslate('The enemy gains ${amount} ${power_icon} from ${card_name}');
        $notification = new NotificationMessage($message, [
            'amount' => abs($newValue - $oldValue),
            'card' => $source,
        ]);
        $this->game->forceTrack->set($newValue, $notification);
    }

    public function gainShuttle(): void
    {
        $shuttleType = $this->getFaction() === FACTION_EMPIRE
            ? CardIds::IMPERIAL_SHUTTLE
            : CardIds::ALLIANCE_SHUTTLE;

        foreach ($this->getCards(ZONE_SOLO_ENEMY_RESERVE) as $card) {
            if ($card->typeArg !== $shuttleType) {
                continue;
            }

            $this->moveCard($card, ZONE_SOLO_ENEMY_SHUTTLES);
            $this->game->notify->all(
                'onSoloEnemyGainShuttle',
                clienttranslate('The enemy gains a Shuttle'),
                ['card' => $card, 'destination' => ZONE_SOLO_ENEMY_SHUTTLES],
            );
            return;
        }
    }

    public function gainCardToMuster(int $cardTypeId): void
    {
        foreach ($this->getCards(ZONE_SOLO_ENEMY_RESERVE) as $card) {
            if ($card->typeArg !== $cardTypeId) {
                continue;
            }

            $this->moveCard($card, ZONE_SOLO_ENEMY_MUSTER_VISIBLE);
            $this->game->notify->all(
                'onSoloEnemyCardMoved',
                clienttranslate('The enemy gains ${card_name} at Muster'),
                ['card' => $card, 'destination' => ZONE_SOLO_ENEMY_MUSTER_VISIBLE],
            );
            return;
        }
    }

    public function gainLeader(): bool
    {
        $leader = $this->getCards(ZONE_SOLO_ENEMY_LEADER)[0] ?? null;
        if ($leader === null) {
            return false;
        }

        $this->moveCard($leader, ZONE_SOLO_ENEMY_MUSTER_VISIBLE);
        $this->game->notify->all(
            'onSoloEnemyLeaderGained',
            clienttranslate('The enemy gains its Leader at Muster'),
            ['card' => $leader, 'destination' => ZONE_SOLO_ENEMY_MUSTER_VISIBLE],
        );
        return true;
    }

    public function applyProgressReward(string $reward): void
    {
        match ($reward) {
            SOLO_GAIN_FORCE => $this->gainForce(1),
            SOLO_GAIN_SHUTTLE => $this->gainShuttle(),
            SOLO_GAIN_TEMPLE_GUARDIAN => $this->gainCardToMuster(CardIds::TEMPLE_GUARDIAN),
            SOLO_GAIN_INQUISITOR => $this->gainCardToMuster(CardIds::INQUISITOR),
            SOLO_GAIN_LEADER => $this->gainLeader(),
            default => throw new \InvalidArgumentException('Unknown solo progress reward.'),
        };
    }

    /**
     * @return CardInstance[]
     */
    public function getCards(string $zone): array
    {
        return $this->game->cardRepository->getSoloEnemyCards($zone);
    }

    public function getActiveBase(): ?CardInstance
    {
        return $this->game->cardRepository->getSoloEnemyActiveBase();
    }

    public function moveCard(CardInstance $card, string $zone): void
    {
        $this->game->cardRepository->moveCardToSoloEnemyZone($card->id, $zone);
    }

    public function getRepository(): CardRepository
    {
        return $this->game->cardRepository;
    }
}