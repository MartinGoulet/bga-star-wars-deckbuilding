<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\NotificationMessage;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

final class GameContext {
    public bool $hasChangeState = false;

    public function __construct(private Game $game, private int $activePlayerId) {
    }

    public function changeState(string $stateName): void {
        $this->hasChangeState = true;
        $this->game->gamestate->jumpToState($stateName);
    }

    public function setGlobalVariable(string $name, mixed $value): void {
        $this->game->globals->set($name, $value);
    }

    public function currentPlayer(): PlayerContext {
        return new PlayerContext($this->game, $this->activePlayerId);
    }
}

final class PlayerContext {
    private string $faction;

    public function __construct(private Game $game, private int $playerId) {
        $sql = "SELECT player_faction FROM player WHERE player_id = {$playerId}";
        $this->faction = $this->game->getUniqueValueFromDB($sql);
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
        $cards = $this->game->cardRepository->drawCardsForPlayer($this->playerId, $count);
        $this->game->notify->all(
            'onDrawCards',
            clienttranslate('${player_name} draws ${count} card(s)'),
            [
                'player_id' => $this->playerId,
                'count' => $count,
                'cards' => array_values(array_map(fn($c) => $c->getOnlyId(), $cards)),
                '_private' => [
                    $this->playerId => new NotificationMessage(
                        clienttranslate('${player_name} draws ${_private.cards_names}'),
                        [
                            'player_id' => $this->playerId,
                            'cards_names' => implode(', ', array_map(fn($c) => $c->name, $cards)),
                            'cards' => $cards,
                            'i18n' => ['cards_names']
                        ]
                    )
                ]
            ]
        );
    }

    public function isForceWithYou(): bool {
        $currentValue = $this->game->forceTrack->get();
        return ($this->faction === FACTION_REBEL && $currentValue > 0)
            || ($this->faction === FACTION_EMPIRE && $currentValue < 0);
    }
}
