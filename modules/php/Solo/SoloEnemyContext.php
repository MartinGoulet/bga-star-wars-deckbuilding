<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\Solo;

use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Cards\CardRepository;
use Bga\Games\StarWarsDeckbuilding\Game;
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