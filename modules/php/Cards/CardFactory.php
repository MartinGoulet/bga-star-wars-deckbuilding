<?php

namespace Bga\Games\StarWarsDeckbuilding\Cards;

use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

final class CardFactory {
    public static function create(
        int $cardId,
        string $cardType,
        int $cardTypeArg,
        string $location,
        int $locationArg,
        int $damage
    ): CardInstance {

        $card_types = Game::get()->card_types;
        $base_types = Game::get()->all_bases;

        // if($cardId === 15) {
        // $card_types = array_map(fn($c) => $c['name'], Game::get()->card_types);
        // var_dump($card_types);
        // die();
        // }

        if ($cardType === "BASE") {
            $cardType = $base_types[$cardTypeArg];
            $instance = new CardInstance(
                id: $cardId,
                typeArg: $cardTypeArg,
                location: $location,
                locationArg: $locationArg,
                name: $cardType['name'],
                type: "BASE",
                img: $cardType['img'],
                faction: $cardType['faction'],
                unique: true,
                cost: 0,
                power: 0,
                force: 0,
                resource: 0,
                damage: $damage,
                health: $cardType['health'],
                abilities: $cardType['abilities'],
                rewards: $cardType['rewards'] ?? [],
                traits: $cardType['traits'] ?? [],
            );

            return $instance;
        }

        if (!isset($card_types[$cardTypeArg]['name'])) {

            return new CardInstance(
                id: $cardId,
                typeArg: $cardTypeArg,
                location: $location,
                locationArg: $locationArg,
                name: '',
                type: '',
                img: 63,
                faction: '',
                unique: false,
                cost: 0,
                power: 0,
                force: 0,
                resource: 0,
                damage: $damage,
                health: 0,
                abilities: [],
                rewards: [],
                traits: [],
            );

            $card_types = array_map(fn($c) => $c['name'], Game::get()->card_types);

            var_dump([
                'cardTypeArg' => $cardTypeArg,
                'card_types' => $card_types,
            ]);
            throw new \InvalidArgumentException("Unknown card type arg: $cardTypeArg");
        } else {
            $cardType = $card_types[$cardTypeArg];
        }

        if(!isset($cardType['cost'])) {
            die('missing cost for '.$cardType['name']);
        }

        $health = 0;
        if(isset($cardType['health'])) {
            $health = $cardType['health'];
        } else {
            if ($cardType['faction'] == FACTION_NEUTRAL) {
                if ($cardType['type'] == CARD_TYPE_SHIP) {
                    $health = $cardType['cost'];
                } else {
                    $health = 0;
                }
            } else {
                $health = $cardType['cost'];
            }
        }
        

        $instance = new CardInstance(
            id: $cardId,
            typeArg: $cardTypeArg,
            location: $location,
            locationArg: $locationArg,
            name: $cardType['name'],
            type: $cardType['type'],
            img: $cardType['img'],
            faction: $cardType['faction'],
            unique: isset($cardType['unique']) ? $cardType['unique'] : false,
            cost: isset($cardType['cost']) ? $cardType['cost'] : 0,
            power: isset($cardType['stats']['power']) ? $cardType['stats']['power'] : 0,
            force: isset($cardType['stats']['force']) ? $cardType['stats']['force'] : 0,
            resource: isset($cardType['stats']['resource']) ? $cardType['stats']['resource'] : 0,
            damage: $damage,
            health: $health,
            abilities: $cardType['abilities'],
            rewards: $cardType['rewards'] ?? [],
            traits: $cardType['traits'] ?? [],
        );

        return $instance;
    }
}
