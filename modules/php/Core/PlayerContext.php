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

    public function gainForce(int $amount, CardInstance $card, string $message = ""): void {
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
            'card' => $card,
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

    public function moveCardToDiscard(CardInstance $card): void {
        $this->game->cardRepository->addCardToPlayerDiscard($card->id, $this->playerId);
        $this->game->notify->all(
            'onMoveCardToDiscard',
            clienttranslate('${player_name} discards ${card_name}'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
            ]
        );
    }

    public function moveCardToExile(int $cardId): void {
        $this->game->cardRepository->addCardToExile($cardId);

        $card = $this->game->cardRepository->getCardById($cardId);
        $this->game->notify->all(
            'onExileCard',
            clienttranslate('${player_name} exiles ${card_name}'),
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
                            'cards' => $cards,
                            'card_names' => array_map(fn($c) => $c->name, $cards),
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

    public function destroyCard(CardInstance $card): void {
        $this->game->cardRepository->addCardToPlayerDiscard($card->id, $this->playerId);
        $card = $this->game->cardRepository->getCard($card->id);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} destroys ${card_names}'),
            [
                'player_id' => $this->playerId,
                'cards' => [$card],
                'destination' => ZONE_PLAYER_DISCARD,
            ]
        );
    }

    public function discardCards(array $cardIds): void {
        $this->game->cardRepository->addCardsToPlayerDiscard($cardIds, $this->playerId);
        $cards = $this->game->cardRepository->getCardsByIds($cardIds);

        $this->game->notify->all(
            'onDiscardCards',
            clienttranslate('${player_name} discards ${card_names}'),
            [
                'player_id' => $this->playerId,
                'cards' => array_values($cards),
                'destination' => ZONE_PLAYER_DISCARD,
            ]
        );
    }

    public function moveCardToTopOfDeck(CardInstance $card): void {
        $this->game->cardRepository->addCardToTopOfDeck($card->id, $this->playerId);
        $card = $this->game->cardRepository->getCard($card->id);
        $this->game->notify->all(
            'onMoveCardToTopOfDeck',
            clienttranslate('${player_name} moves ${card_name} to the top of their deck'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
                'destination' => ZONE_PLAYER_DECK,
            ]
        );
    }

    public function moveCardToGalaxyDiscard(CardInstance $card): void {
        $this->game->cardRepository->addCardToGalaxyDiscard($card->id);
        $card = $this->game->cardRepository->getCard($card->id);
        $this->game->notify->all(
            'onMoveCardToGalaxyDiscard',
            clienttranslate('${player_name} moves ${card_name} to the galaxy discard pile'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
            ]
        );
    }

    public function moveCardToGalaxyRow(CardInstance $card): void {
        $this->game->cardRepository->addCardToGalaxyRow($card->id); 
        $card = $this->game->cardRepository->getCard($card->id);
        $this->game->notify->all(
            'onMoveCardToGalaxyRow',
            clienttranslate('${player_name} moves ${card_name} to the galaxy row'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
            ]
        );
    }

    public function moveCardToGalaxyDeck(CardInstance $card): void {
        $this->game->cardRepository->addCardToTopOfDeck($card->id, 0);
        $card = $this->game->cardRepository->getCard($card->id);
        $this->game->notify->all(
            'onMoveCardToGalaxyDeck',
            clienttranslate('${player_name} moves ${card_name} on top of the galaxy deck'),
            [
                'player_id' => $this->playerId,
                'card' => $card,
            ]
        );
    }
}
