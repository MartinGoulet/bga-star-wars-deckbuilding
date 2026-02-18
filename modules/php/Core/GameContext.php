<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\Db\Globals;
use Bga\Games\StarWarsDeckbuilding\Cards\CardRepository;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetResolver;
use CardInstance;

final class GameContext {
    private const GALAXY_ROW_SIZE = 6;
    private int $activePlayerId;

    public bool $hasChangeState = false;
    public CardRepository $cardRepository;
    public Globals $globals;
    public TargetResolver $targetResolver;

    public ?array $event = null;

    public function __construct(public Game $game) {
        $this->activePlayerId = intval($game->getActivePlayerId());
        $this->cardRepository = $this->game->cardRepository;
        $this->globals = $this->game->globals;
        $this->targetResolver = new TargetResolver($this);
    }

    public function withEvent(array $event): self {
        $newContext = clone $this;
        $newContext->event = $event;
        return $newContext;
    }

    public function changeState(int $stateNumber): void {
        $this->hasChangeState = true;
        $this->game->gamestate->jumpToState($stateNumber);
    }

    public function setGlobalVariable(string $name, mixed $value): void {
        $this->game->globals->set($name, $value);
    }

    public function currentPlayer(): PlayerContext {
        return new PlayerContext($this->game, $this->activePlayerId);
    }

    public function opponentPlayer(): PlayerContext {
        return new PlayerContext($this->game, $this->getOpponentId());
    }

    public function galaxy(): GalaxyContext {
        return new GalaxyContext($this);
    }

    public function getOpponentId(): int {
        $sql = "SELECT player_id FROM player WHERE player_id != {$this->activePlayerId}";
        return intval($this->game->getUniqueValueFromDb($sql));
    }

    public function refillGalaxyRow(): void {
        $galaxyRow = $this->game->cardRepository->getGalaxyRow();
        $neededCards = self::GALAXY_ROW_SIZE - count($galaxyRow);

        if ($neededCards > 0) {
            $newCards = $this->game->cardRepository->drawCardsFromGalaxyDeck($neededCards);
            $this->game->notify->all(
                'onRefillGalaxyRow',
                clienttranslate('Refilling Galaxy Row with ${num} card(s). ${card_names}'),
                [
                    'num' => count($newCards),
                    'newCards' => array_values($newCards),
                    'card_names' => array_map(fn($card) => $card->name, $newCards),
                    'i18n' => ['card_names'],
                ]
            );
        }
    }

    /** 
     * @param int $playerId
     * @param string[] $zone
     * @return CardInstance[]
     */
    public function getSelectableCards(int $playerId, array $zone): array {

        $selectableCards = [];
        foreach ($zone as $z) {
            switch ($z) {
                case ZONE_HAND:
                    $cards = $this->game->cardRepository->getPlayerHand($playerId);
                    break;
                case ZONE_PLAYER_PLAY_AREA:
                    $cards = $this->game->cardRepository->getPlayerPlayArea($playerId);
                    break;
                case ZONE_DISCARD:
                    $cards = $this->game->cardRepository->getPlayerDiscardPile($playerId);
                    break;
                default:
                    throw new \BgaUserException("GameContext: Invalid zone for selectable cards: $z");
            }
            foreach ($cards as $card) {
                $selectableCards[$card->id] = $card;
            }
        }
        return $selectableCards;
    }

    private function getPlayerByTarget(string $target): PlayerContext {
        return match ($target) {
            TARGET_SELF => $this->currentPlayer(),
            TARGET_OPPONENT => $this->opponentPlayer(),
            default => throw new \InvalidArgumentException("Invalid target for selectable cards: $target"),
        };
    }


    public function exileCard(int $cardId): void {
        $this->game->cardRepository->addCardToExile($cardId);

        $card = $this->cardRepository->getCardById($cardId);
        $this->game->notify->all(
            'onExileCard',
            clienttranslate('${player_name} exiles card ${card_name}'),
            [
                'player_id' => $this->activePlayerId,
                'card' => $card,
            ]
        );
    }

    public function getGameEngine(): GameEngine {
        return new GameEngine($this->game, $this);
    }

    /**
     * @param CardInstance $target
     * @param int $amount
     * @return int Remaining damage after applying to target
     */
    public function assignDamageToTarget(CardInstance $target, int $amount): int {
        $remaining = 0;
        $target->damage += $amount;
        if ($target->damage > $target->health) {
            $remaining = $target->damage - $target->health;
            $target->damage = $target->health;
        }
        $damages = $this->game->globals->get(GVAR_DAMAGE_ON_CARDS, []);
        $this->globals->set(GVAR_REMAINING_DAMAGE_TO_ASSIGN, $remaining);

        if ($target->damage <= $target->health) {
            $damages[$target->id] = $target->damage;
            $this->game->globals->set(GVAR_DAMAGE_ON_CARDS, $damages);

            $this->game->notify->all(
                'onDealDamageToCard',
                clienttranslate('${player_name} deals ${damage} damage to ${card_name} (total damage: ${total_damage}/${health})'),
                [
                    'player_id' => $this->game->getActivePlayerId(),
                    'card' => $target,
                    'damage' => ($amount - $remaining),
                    'health' => $target->health,
                    'total_damage' => $target->damage,
                ]
            );
        } else if (isset($damages[$target->id])) {
            unset($damages[$target->id]);
        }


        return $remaining;
    }
}
