<?php

namespace Bga\Games\StarWarsDeckbuilding\Cards;

use Bga\GameFramework\Components\Deck;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardIds;
use CardInstance;


final class CardRepository {

    private const GALAXY_ROW_SIZE = 6;

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

    public function addCardToGalaxyRow(int $cardId, ?int $locationArg = null): int {
        $availablePositions = $this->getAvailableGalaxyRowPositions();

        if ($locationArg === null) {
            $locationArg = $availablePositions[0] ?? null;
        }

        if ($locationArg === null || !in_array($locationArg, $availablePositions, true)) {
            throw new \InvalidArgumentException('Cannot add a card to the Galaxy Row at the requested position.');
        }

        $this->deck->insertCard($cardId, ZONE_GALAXY_ROW, $locationArg);
        return $locationArg;
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
    public function drawCardsFromGalaxyDeck(int $count, ?array $locationArgs = null): array {
        if ($count <= 0) {
            return [];
        }

        $availablePositions = $this->getAvailableGalaxyRowPositions();
        $locationArgs ??= array_slice($availablePositions, 0, $count);

        if (count($locationArgs) < $count) {
            throw new \InvalidArgumentException('Not enough available positions in the Galaxy Row.');
        }

        $cards = [];
        foreach (array_slice($locationArgs, 0, $count) as $locationArg) {
            if (!in_array($locationArg, $availablePositions, true)) {
                throw new \InvalidArgumentException('Cannot draw a card to the requested Galaxy Row position.');
            }

            $card = $this->deck->pickCardForLocation(
                ZONE_GALAXY_DECK,
                ZONE_GALAXY_ROW,
                $locationArg,
            );

            if ($card === null) {
                break;
            }

            $cards[] = $this->getCard((int) $card['id']);
            $availablePositions = array_values(array_diff($availablePositions, [$locationArg]));
        }

        return $cards;
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

    /**
     * @return CardInstance[]
     */
    public function getSoloEnemyCards(string $zone): array {
        $cards = $this->deck->getCardsInLocation($zone, null, 'card_location_arg');
        return array_map(fn($row) => $this->createFromRow($row), $cards);
    }

    public function getSoloEnemyActiveBase(): CardInstance|null {
        $card = $this->deck->getCardOnTop(ZONE_SOLO_ENEMY_ACTIVE_BASE);
        return $card === null ? null : $this->createFromRow($card);
    }

    public function moveCardToSoloEnemyZone(int $cardId, string $zone): void {
        $this->deck->insertCardOnExtremePosition($cardId, $zone, true);
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

    public function setup(array $players, ?string $soloEnemyFaction = null, int $soloShuttleCount = 3): void {

        if (count($players) === 1) {
            $this->setupSolo($players, $soloEnemyFaction, $soloShuttleCount);
            return;
        }

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

        $this->drawCardsFromGalaxyDeck(self::GALAXY_ROW_SIZE, range(1, self::GALAXY_ROW_SIZE));

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

    private function setupSolo(array $players, ?string $soloEnemyFaction, int $soloShuttleCount): void {
        if ($soloEnemyFaction === null) {
            throw new \InvalidArgumentException('The solo enemy faction is required.');
        }

        $leaderType = $soloEnemyFaction === FACTION_EMPIRE
            ? CardIds::DARTH_VADER
            : CardIds::LUKE_SKYWALKER;

        // The enemy leader is removed before the Galaxy deck is shuffled.
        $cards = [];
        foreach ($this->game->galaxy_deck_composition as $cardTypeId => $amount) {
            if ($cardTypeId === $leaderType) {
                continue;
            }
            $cards[] = [
                'type' => $this->game->card_types[$cardTypeId]['type'],
                'type_arg' => $cardTypeId,
                'nbr' => $amount,
            ];
        }
        $this->deck->createCards($cards, ZONE_GALAXY_DECK);
        $this->deck->shuffle(ZONE_GALAXY_DECK);
        $this->drawCardsFromGalaxyDeck(self::GALAXY_ROW_SIZE, range(1, self::GALAXY_ROW_SIZE));

        $this->deck->createCards([
            [
                'type' => CARD_TYPE_UNIT,
                'type_arg' => CardIds::OUTER_RIM_PILOT,
                'nbr' => 10,
            ],
        ], ZONE_OUTER_RIM_DECK);

        foreach ($players as $playerId => $player) {
            $cards = [];
            foreach ($this->game->starter_decks[$player['faction']] as $cardTypeId => $amount) {
                $cards[] = [
                    'type' => 'STARTER',
                    'type_arg' => $cardTypeId,
                    'nbr' => $amount,
                ];
            }
            $this->deck->createCards($cards, 'deck_' . $playerId);
            $this->deck->shuffle('deck_' . $playerId);
            $this->deck->pickCardsForLocation(5, 'deck_' . $playerId, ZONE_HAND, $playerId);
        }

        foreach ($players as $playerId => $player) {
            $startingBaseType = null;
            $cards = [];
            foreach ($this->game->base_decks[$player['faction']] as $cardTypeId => $baseInfo) {
                if (isset($baseInfo['beginner'])) {
                    $cards[] = [
                        'type' => CARD_TYPE_BASE,
                        'type_arg' => $cardTypeId,
                        'nbr' => 1,
                    ];
                }

                if (isset($baseInfo['starting_base'])) {
                    $startingBaseType = (int) $cardTypeId;
                }
            }

            if ($startingBaseType === null) {
                throw new \InvalidArgumentException('The player base deck is incomplete.');
            }

            $this->deck->createCards($cards, 'base_' . $playerId);
            $startingBaseCards = $this->deck->getCardsOfType(CARD_TYPE_BASE, $startingBaseType);
            $startingBase = array_shift($startingBaseCards);
            if ($startingBase === null) {
                throw new \InvalidArgumentException('The player starting base could not be created.');
            }
            $this->deck->moveCard($startingBase['id'], 'ab_' . $playerId);
        }

        $enemyBases = $this->game->base_decks[$soloEnemyFaction];
        $startingBaseType = null;
        $nonStartingBaseTypes = [];
        foreach ($enemyBases as $cardTypeId => $baseInfo) {
            if (isset($baseInfo['starting_base'])) {
                $startingBaseType = $cardTypeId;
            } else {
                $nonStartingBaseTypes[] = $cardTypeId;
            }
        }

        if ($startingBaseType === null || count($nonStartingBaseTypes) < 2) {
            throw new \InvalidArgumentException('The solo enemy base deck is incomplete.');
        }

        shuffle($nonStartingBaseTypes);
        $this->deck->createCards([
            [
                'type' => 'BASE',
                'type_arg' => $startingBaseType,
                'nbr' => 1,
            ],
        ], ZONE_SOLO_ENEMY_ACTIVE_BASE);
        $this->deck->createCards(array_map(
            fn(int $cardTypeId) => [
                'type' => 'BASE',
                'type_arg' => $cardTypeId,
                'nbr' => 1,
            ],
            array_slice($nonStartingBaseTypes, 0, 2),
        ), ZONE_SOLO_ENEMY_BASES);

        $this->deck->createCards([
            [
                'type' => CARD_TYPE_UNIT,
                'type_arg' => $leaderType,
                'nbr' => 1,
            ],
        ], ZONE_SOLO_ENEMY_LEADER);

        // Create the complete starter set in reserve, then move only the visible starters.
        $enemyStarterCards = [];
        foreach ($this->game->starter_decks[$soloEnemyFaction] as $cardTypeId => $amount) {
            $enemyStarterCards[] = [
                'type' => 'STARTER',
                'type_arg' => $cardTypeId,
                'nbr' => $amount,
            ];
        }
        $this->deck->createCards($enemyStarterCards, ZONE_SOLO_ENEMY_RESERVE);
        $reserveCards = $this->deck->getCardsInLocation(ZONE_SOLO_ENEMY_RESERVE);

        $shuttleIds = [];
        $trooperIds = [];
        foreach ($reserveCards as $card) {
            if ((int) $card['type_arg'] === ($soloEnemyFaction === FACTION_EMPIRE ? CardIds::IMPERIAL_SHUTTLE : CardIds::ALLIANCE_SHUTTLE)) {
                $shuttleIds[] = (int) $card['id'];
            }
            if ((int) $card['type_arg'] === ($soloEnemyFaction === FACTION_EMPIRE ? CardIds::STORMTROOPER : CardIds::REBEL_TROOPER)) {
                $trooperIds[] = (int) $card['id'];
            }
        }

        foreach (array_slice($shuttleIds, 0, $soloShuttleCount) as $cardId) {
            $this->deck->moveCard($cardId, ZONE_SOLO_ENEMY_SHUTTLES);
        }
        if (isset($trooperIds[0])) {
            $this->deck->moveCard($trooperIds[0], ZONE_SOLO_ENEMY_MUSTER_VISIBLE);
        }
        if (isset($trooperIds[1])) {
            $this->deck->moveCard($trooperIds[1], ZONE_SOLO_ENEMY_MUSTER_HIDDEN);
        }
    }

    /**
     * @return int[]
     */
    public function getAvailableGalaxyRowPositions(): array {
        $occupiedPositions = array_map(
            fn(CardInstance $card) => $card->locationArg,
            $this->getGalaxyRow(),
        );

        return array_values(array_diff(range(1, self::GALAXY_ROW_SIZE), $occupiedPositions));
    }
}
