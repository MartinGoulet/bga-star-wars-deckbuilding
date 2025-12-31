<?php

namespace Bga\Games\StarWarsDeckbuilding\Cards;

use Bga\GameFramework\Components\Deck;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;


final class CardRepository {
    public function __construct(private Game $game, private Deck $deck) {
    }

    public function addCardToPlayArea(int $cardId, int $playerId): void {
        $this->deck->insertCardOnExtremePosition($cardId, ZONE_PLAYER_PLAY_AREA . $playerId, true);
    }

    public function addCardToPlayerDiscard(int $cardId, int $playerId): void {
        $this->deck->insertCardOnExtremePosition($cardId, 'discard_' . $playerId, true);
    }

    public function addCardToPlayerHand(int $cardId, int $playerId): void {
        $this->deck->moveCard($cardId, 'hand', $playerId);
    }

    public function countGalaxyDeck(): int {
        return $this->deck->countCardsInLocation(ZONE_GALAXY_DECK);
    }

    public function countPlayerDeck(int $playerId): int {
        return $this->deck->countCardsInLocation('deck_' . $playerId);
    }

    /**
     * @return CardInstance[]
     */
    public function drawCardsForPlayer(int $playerId, int $count): array {
        $cards = $this->deck->pickCardsForLocation($count, 'deck_' . $playerId, ZONE_HAND, $playerId);
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    public function getCard(int $cardId): CardInstance {
        $row = $this->deck->getCard($cardId);
        return self::createFromRow($row);
    }

    public function getCardById(int $cardId): CardInstance {
        $row = $this->deck->getCard($cardId);
        return self::createFromRow($row);
    }

    public function getActiveBase(int $playerId): CardInstance | null {
        $card = $this->deck->getCardOnTop('ab_' . $playerId);
        if ($card === null) {
            return null;
        }
        return self::createFromRow($card);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerDiscardPile(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('discard_' . $playerId);
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerPlayArea(int $playerId): array {
        $cards = $this->deck->getCardsInLocation(ZONE_PLAYER_PLAY_AREA . $playerId, null, 'card_location_arg');
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    public function getPlayerShips(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('ships_' . $playerId);
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    public function getGalaxyDiscardPile(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_GALAXY_DISCARD);
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getGalaxyRow(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_GALAXY_ROW, null, 'card_location_arg');
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    public function getPlayerHand(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('hand', $playerId);
        return array_map(fn($row) => self::createFromRow($row), $cards);
    }

    private static function createFromRow(array $row): CardInstance {
        return CardFactory::create(
            intval($row['id']),
            $row['type'],
            intval($row['type_arg']),
            $row['location'],
            intval($row['location_arg'])
        );
    }

    public function setup(array $players): void {

        // Setup galaxy deck
        $cards = [];
        foreach ($this->game->galaxy_deck_composition as $card_type_id => $amount) {
            $type = $this->game->card_types[$card_type_id]['type'];
            $cards[] = [
                'type' => $type,
                'type_arg' => $card_type_id,
                'nbr' => $amount,
            ];
        }

        $this->deck->createCards($cards, ZONE_DECK);
        $this->deck->shuffle(ZONE_DECK);

        for ($i = 0; $i < 6; $i++) {
            $card = $this->deck->getCardOnTop(ZONE_DECK);
            $this->deck->insertCardOnExtremePosition($card['id'], ZONE_GALAXY_ROW, true);
        }

        // Setup player decks
        foreach ($players as $player_id => $player) {
            $cards = [];
            foreach ($this->game->starter_decks[$player['faction']] as $card_type_id => $amount) {
                $cards[] = [
                    'type' => 'STARTER',
                    'type_arg' => $card_type_id,
                    'nbr' => $amount,
                ];
            }
            $this->deck->createCards($cards, 'deck_' . $player_id);
            $this->deck->shuffle('deck_' . $player_id);
            $this->deck->pickCardsForLocation(5, 'deck_' . $player_id, ZONE_HAND, $player_id);
        }

        // Setup bases
        foreach ($players as $player_id => $player) {
            $cards = [];
            $starting_base = 0;
            foreach ($this->game->base_decks[$player['faction']] as $card_type_id => $base_info) {
                if (isset($base_info['beginner'])) {
                    $cards[] = [
                        'type' => 'BASE',
                        'type_arg' => $card_type_id,
                        'nbr' => $amount,
                    ];
                }

                if (isset($base_info['starting_base'])) {
                    $starting_base = intval($card_type_id);
                }
            }
            $this->deck->createCards($cards, 'base_' . $player_id);
            $cards = $this->deck->getCardsOfType('BASE', $starting_base);
            $card = array_shift($cards);
            // ab stands for "active base"
            $this->deck->moveCard($card['id'], 'ab_' . $player_id);
        }
    }
}
