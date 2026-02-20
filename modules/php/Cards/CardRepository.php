<?php

namespace Bga\Games\StarWarsDeckbuilding\Cards;

use Bga\GameFramework\Components\Deck;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardIds;
use CardInstance;


final class CardRepository {

    public array | null $damageOnCards = null;

    public function __construct(private Game $game, private Deck $deck) {
    }

    public function addBaseCardToPlayer(int $cardId, int $playerId): void {
        $this->deck->moveCard($cardId, 'ab_' . $playerId);
    }

    public function addCardToExile(int $cardId): void {
        $this->deck->insertCardOnExtremePosition($cardId, ZONE_EXILE, true);
    }

    public function addCardToPlayArea(int $cardId, int $playerId): void {
        $this->deck->insertCardOnExtremePosition($cardId, ZONE_PLAYER_PLAY_AREA . $playerId, true);
    }

    public function addCardToTopOfDeck(int $cardId, int $playerId): void {
        if($playerId === 0) {
            $this->deck->insertCardOnExtremePosition($cardId, ZONE_GALAXY_DECK, true);
        } else {
            $this->deck->insertCardOnExtremePosition($cardId, 'deck_' . $playerId, true);
        }
    }

    public function addCardToShipArea(int $cardId, int $playerId): void {
        $this->deck->insertCardOnExtremePosition($cardId, 'ships_' . $playerId, true);
    }

    public function addCardToPlayerDiscard(int $cardId, int $playerId): void {
        $this->deck->insertCardOnExtremePosition($cardId, 'discard_' . $playerId, true);
    }

    public function addCardsToPlayerDiscard(array $cardIds, int $playerId): void {
        foreach ($cardIds as $cardId) {
            $this->deck->insertCardOnExtremePosition($cardId, 'discard_' . $playerId, true);
        }
    }

    public function addCardToPlayerHand(int $cardId, int $playerId): void {
        $this->deck->moveCard($cardId, 'hand', $playerId);
    }

    public function addCardToGalaxyDiscard(int $cardId): void {
        $this->deck->insertCardOnExtremePosition($cardId, ZONE_GALAXY_DISCARD, true);
    }

    public function addCardToGalaxyRow(int $cardId): void {
        $this->deck->insertCardOnExtremePosition($cardId, ZONE_GALAXY_ROW, true);
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
    public function getGalaxyDeckUI(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_GALAXY_DECK, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row)->getUI(), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function drawCardsForPlayer(int $playerId, int $count): array {
        $cards = $this->deck->pickCardsForLocation($count, 'deck_' . $playerId, ZONE_HAND, $playerId);
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function drawCardsFromGalaxyDeck(int $count): array {
        $cards = $this->deck->pickCardsForLocation($count, ZONE_GALAXY_DECK, ZONE_GALAXY_ROW);
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    public function getCard(int $cardId): CardInstance {
        $row = $this->deck->getCard($cardId);
        return $this->createFromRow($row);
    }

    public function getCardById(int $cardId): CardInstance {
        $row = $this->deck->getCard($cardId);
        return $this->createFromRow($row);
    }

    /**
     * @return CardInstance[]
     */
    public function getCardsByIds(array $cardIds): array {
        $rows = $this->deck->getCards($cardIds);
        return array_map(fn($row) => $this->createFromRow($row), $rows);
    }

    public function getActiveBase(int $playerId): CardInstance | null {
        $card = $this->deck->getCardOnTop('ab_' . $playerId);
        if ($card === null) {
            return null;
        }
        return $this->createFromRow($card);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerBaseDeck(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('base_' . $playerId);
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerDiscardPile(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('discard_' . $playerId, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerPlayArea(int $playerId): array {
        $cards = $this->deck->getCardsInLocation(ZONE_PLAYER_PLAY_AREA . $playerId, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerShips(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('ships_' . $playerId, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getGalaxyDiscardPile(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_GALAXY_DISCARD, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getGalaxyDeckTopCards(int $count): array {
        $cards = $this->deck->getCardsOnTop($count, ZONE_GALAXY_DECK);
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getGalaxyRow(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_GALAXY_ROW, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    public function getOuterRimDeck(): array {
        $cards = $this->deck->getCardsInLocation(ZONE_OUTER_RIM_DECK, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    /**
     * @return CardInstance[]
     */
    public function getPlayerHand(int $playerId): array {
        $cards = $this->deck->getCardsInLocation('hand', $playerId);
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    public function getCardOnTopOfGalaxyDeck(): CardInstance | null {
        $card = $this->deck->getCardOnTop(ZONE_GALAXY_DECK);
        if ($card === null) {
            return null;
        }
        return $this->createFromRow($card);
    }

    private function createFromRow(array $row): CardInstance {
        if ($this->damageOnCards == null) {
            $this->damageOnCards = $this->game->globals->get(GVAR_DAMAGE_ON_CARDS, []);
        }
        $damage = isset($this->damageOnCards[$row['id']]) ? $this->damageOnCards[$row['id']] : 0;
        return CardFactory::create(
            intval($row['id']),
            $row['type'],
            intval($row['type_arg']),
            $row['location'],
            intval($row['location_arg']),
            $damage
        );
    }

    public function reshufflePlayerDiscardIntoDeck(int $playerId): void {
        $this->deck->moveAllCardsInLocation('discard_' . $playerId, 'deck_' . $playerId);
        $this->deck->shuffle('deck_' . $playerId);
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

        $this->drawCardsFromGalaxyDeck(6);

        // Setup outer rim row
        $cards = [];
        $cards[] = [
            'type' => CARD_TYPE_UNIT,
            'type_arg' => CardIds::OUTER_RIM_PILOT,
            'nbr' => 10,
        ];
        $this->deck->createCards($cards, ZONE_OUTER_RIM_DECK);

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
