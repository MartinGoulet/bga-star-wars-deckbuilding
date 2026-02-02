<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

final class PlayerContext {
    private string $faction;

    public function __construct(private Game $game, public int $playerId) {
        $sql = "SELECT player_faction FROM player WHERE player_id = {$playerId}";
        $this->faction = $this->game->getUniqueValueFromDB($sql);
    }

    public function getFaction(): string {
        return $this->faction;
    }

    public function addResources(int $amount, string $message = ""): void {
        if ($message === "") {
            $message = clienttranslate('${player_name} gains ${amount} Resource(s)');
        }
        $notif = new NotificationMessage($message, [
            'player_id' => $this->playerId,
            'amount' => $amount,
        ]);
        // $currentValue = $this->game->playerResources->get($this->playerId);
        // $this->game->playerResources->set($this->playerId, $currentValue + $amount, $notif);
        $this->game->playerResources->inc($this->playerId, $amount, $notif);
    }

    public function gainForce(int $amount, string $message = ""): void {
        $originalAmount = $amount;
        $amount = $this->faction === FACTION_EMPIRE ? -$amount : $amount;

        $currentValue = $this->game->forceTrack->get();
        if (($currentValue + $amount) < -3 || ($currentValue + $amount) > 3) {
            return;
        }

        if ($message === "") {
            $message = clienttranslate('${player_name} gains ${amount} Power');
        }

        $notif = new NotificationMessage($message, [
            'player_id' => $this->playerId,
            'amount' => $originalAmount,
        ]);

        $this->game->forceTrack->set($currentValue + $amount, $notif);
    }

    public function moveCardToHand(CardInstance $card): void {
        $this->game->cardRepository->addCardToPlayerHand($card->id, $this->playerId);
        $this->game->notify->all(
            'onMoveCardToHand',
            clienttranslate('${player_name} moves ${card_name} to their hand'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
            ]
        );
    }

    public function drawCards(int $count): void {
        $playerDeckCount = $this->game->cardRepository->countPlayerDeck($this->playerId);
        if ($playerDeckCount < $count) {
            $cards = $this->game->cardRepository->drawCardsForPlayer($this->playerId, $playerDeckCount);
            $this->notifyDrawCards($cards);
            $this->game->cardRepository->reshufflePlayerDiscardIntoDeck($this->playerId);
            $this->notifyShuffleDiscardIntoDeck($this->playerId);
            $remainingCount = $count - $playerDeckCount;
            if ($remainingCount > 0) {
                $cards = $this->game->cardRepository->drawCardsForPlayer($this->playerId, $remainingCount);
                $this->notifyDrawCards($cards);
            }
            return;
        } else {
            $cards = $this->game->cardRepository->drawCardsForPlayer($this->playerId, $count);
            $this->notifyDrawCards($cards);
        }
    }

    private function notifyDrawCards(array $cards): void {
        if (empty($cards)) return;
        $this->game->notify->all(
            'onDrawCards',
            clienttranslate('${player_name} draws ${count} card(s)'),
            [
                'player_id' => $this->playerId,
                'count' => count($cards),
                'cards' => array_values(array_map(fn($c) => $c->getOnlyId(), $cards)),
                '_private' => [
                    $this->playerId => new NotificationMessage(
                        clienttranslate('${player_name} draws ${_private.card_names}'),
                        [
                            'player_id' => $this->playerId,
                            'card_names' => array_map(fn($c) => $c->name, $cards),
                            'cards' => $cards,
                        ]
                    )
                ]
            ]
        );
    }

    private function notifyShuffleDiscardIntoDeck(int $playerId): void {
        $this->game->notify->all(
            'onShuffleDiscardIntoDeck',
            clienttranslate('${player_name} shuffles their discard pile into their deck'),
            [
                'player_id' => $playerId,
            ]
        );
    }

    public function hasForceWithYou(): bool {
        $currentValue = $this->game->forceTrack->get();
        return ($this->faction === FACTION_REBEL && $currentValue > 0)
            || ($this->faction === FACTION_EMPIRE && $currentValue < 0);
    }

    public function hasForceWithYouForResourceGain(): bool {
        $currentValue = $this->game->forceTrack->get();
        return ($this->faction === FACTION_REBEL && $currentValue === 3)
            || ($this->faction === FACTION_EMPIRE && $currentValue === -3);
    }

    /** @return CardInstance[] */
    public function getCardsInPlayArea(): array {
        return $this->game->cardRepository->getPlayerPlayArea($this->playerId);
    }

    public function getCardsInShipArea(): array {
        return $this->game->cardRepository->getPlayerShips($this->playerId);
    }

    public function discardCards(array $cardIds): void {
        $this->game->cardRepository->addCardsToPlayerDiscard($cardIds, $this->playerId);
        $cards = $this->game->cardRepository->getCardsByIds($cardIds);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} discards ${card_names}'),
            [
                'player_id' => $this->playerId,
                'card_names' => array_values(array_map(fn($c) => $c->name, $cards)),
                'cards' => array_values($cards),
            ]
        );
    }
}
