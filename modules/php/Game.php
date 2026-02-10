<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * StarWarsDeckbuilding implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding;

use Bga\GameFramework\Actions\Debug;
use Bga\GameFramework\Components\Counters\PlayerCounter;
use Bga\GameFramework\Components\Counters\TableCounter;
use Bga\Games\StarWarsDeckbuilding\Cards\CardRepository;
use Bga\Games\StarWarsDeckbuilding\States\PlayerTurn_ActionSelection;
use CardInstance;

require_once('constants.inc.php');
require_once('Cards/CardInstance.php');

class Game extends \Bga\GameFramework\Table {
    public array $card_types;
    public array $starter_decks;
    public array $galaxy_deck_composition;
    public array $base_decks;
    public array $all_bases;

    public TableCounter $forceTrack;
    public PlayerCounter $playerResources;
    public TableCounter $nbrPurchasesThisRound;

    private \Bga\GameFramework\Components\Deck $cards;
    public CardRepository $cardRepository;

    private static Game $instance;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If you want to store any type instead of int, use $this->globals instead.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct() {
        parent::__construct();
        $this->initGameStateLabels([]); // mandatory, even if the array is empty

        $this->playerResources = $this->counterFactory->createPlayerCounter('resources', 0);
        $this->forceTrack = $this->counterFactory->createTableCounter('force', -3, 3);
        $this->nbrPurchasesThisRound = $this->counterFactory->createTableCounter('nbrPurchasesThisRound', 0);

        require('material.inc.php');

        // automatically complete notification args when needed
        $this->notify->addDecorator(function (string $message, array $args) {
            if (isset($args['player_id']) && !isset($args['player_name']) && str_contains($message, '${player_name}')) {
                $args['player_name'] = $this->getPlayerNameById(intval($args['player_id']));
            }

            if (isset($args['card']) && !isset($args['card_name']) && str_contains($message, '${card_name}')) {
                /** @var CardInstance $card */
                $card = $args['card'];
                $args['card_name'] = $card->name;
                $args['i18n'][] = ['card_name'];
            }

            if (isset($args['card_id']) && !isset($args['card_name']) && str_contains($message, '${card_name}')) {
                /** @var CardInstance $card */
                $card = $this->cardRepository->getCard($args['card_id']);
                $args['card_name'] = $card->name;
                $args['i18n'][] = ['card_name'];
            }

            return $args;
        });

        $this->cards = $this->deckFactory->createDeck('card');
        self::$instance = $this;

        $this->cardRepository = new CardRepository($this, $this->cards);
    }

    public static function get(): Game {
        return self::$instance;
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression() {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version) {
        //       if ($from_version <= 1404301345)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "ALTER TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
        //
        //       if ($from_version <= 1405061421)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "CREATE TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas(): array {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score`, `player_faction` `faction`, `player_no` `playerNo` FROM `player`"
        );
        // $this->playerEnergy->fillResult($result);

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        $result['galaxyRow'] = $this->cardRepository->getGalaxyRow();
        $result['galaxyDeckCount'] = $this->cardRepository->countGalaxyDeck();
        $result['galaxyDiscard'] = $this->cardRepository->getGalaxyDiscardPile();
        $result['playerHand'] = array_values($this->cardRepository->getPlayerHand($current_player_id));
        $result['outerRimDeck'] = array_values($this->cardRepository->getOuterRimDeck());
        
        foreach($result["players"] as &$player) {
            $pId = intval($player['id']);
            $player['playAreaCards'] = array_values($this->cardRepository->getPlayerPlayArea($pId));
            $player['deckCount'] = $this->cardRepository->countPlayerDeck($pId);
            $player['discard'] = array_values($this->cardRepository->getPlayerDiscardPile($pId));
            $player['activeBase'] = $this->cardRepository->getActiveBase($pId);
            $player['ships'] = array_values($this->cardRepository->getPlayerShips($pId));
        }

        $this->playerResources->fillResult($result);
        $this->forceTrack->fillResult($result);

        if($this->getBgaEnvironment() == 'studio') {
            $result['debug_cards'] = array_values($this->getCollectionFromDB("SELECT * FROM card"));
        }

        return $result;
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = []) {
        $this->playerResources->initDb(array_keys($players), initialValue: 0);
        $this->forceTrack->initDb(initialValue: 3); // Rebel side
        $this->nbrPurchasesThisRound->initDb(initialValue: 0);

        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        $factions = [FACTION_REBEL, FACTION_EMPIRE];
        shuffle($factions);

        foreach ($players as $player_id => &$player) {
            $player['player_id'] = $player_id;
            $player['faction'] = array_shift($factions);
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
                $player['faction'],
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_faction) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.

        // Dummy content.
        // $this->tableStats->init('table_teststat1', 0);
        // $this->playerStats->init('player_teststat1', 0);

        // TODO: Setup the initial game situation here.
        $this->cardRepository->setup($players);

        // Empire starts first
        $player = array_find($players, fn($p) => $p['faction'] === FACTION_EMPIRE);
        $this->gamestate->changeActivePlayer($player['player_id']);

        return PlayerTurn_ActionSelection::class;
    }

    /**
     * Example of debug function.
     * Here, jump to a state you want to test (by default, jump to next player state)
     * You can trigger it on Studio using the Debug button on the right of the top bar.
     */
    public function debug_goToState(int $state = 3) {
        $this->gamestate->jumpToState($state);
    }

    /**
     * Another example of debug function, to easily test the zombie code.
     */
    public function debug_playOneMove() {
        $this->debug->playUntil(fn(int $count) => $count == 1);
    }

    #[Debug(reload: true)]
    public function debug_resetDeck() {
        $sql = "TRUNCATE TABLE card";
        self::DbQuery($sql);
        $sql = "SELECT player_id, player_faction `faction` FROM player";
        $players = $this->getCollectionFromDb($sql);
        $this->cardRepository->setup($players);
        $this->playerResources->setAll(0);
        $this->forceTrack->set(3);
    }

    #[Debug(reload: true)]
    public function debug_resetAttack() {
        $this->globals->set(GVAR_ATTACKERS_CARD_IDS, []);
        $this->globals->set(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
    }

    #[Debug(reload: false)]
    public function debug_shuffle() {
        $this->cards->shuffle(ZONE_GALAXY_DECK);
    }

    public function debug_initCounter() {
        $this->nbrPurchasesThisRound->initDb(initialValue: 0);
    }

    /*
    Another example of debug function, to easily create situations you want to test.
    Here, put a card you want to test in your hand (assuming you use the Deck component).

    public function debug_setCardInHand(int $cardType, int $playerId) {
        $card = array_values($this->cards->getCardsOfType($cardType))[0];
        $this->cards->moveCard($card['id'], 'hand', $playerId);
    }
    */
}
