<?php

require_once('cards-empire.inc.php');
require_once('cards-rebel.inc.php');
require_once('cards-neutral.inc.php');

$this->card_types = $empire_cards + $rebel_cards + $neutral_cards +
   [
      // Generic
      CardIds::OUTER_RIM_PILOT => [
         'name' => clienttranslate('Outer Rim Pilot'),
         'img' => CardIds::OUTER_RIM_PILOT,
         'type' => CARD_TYPE_UNIT,
         'faction' => FACTION_NEUTRAL,
         'abilities' => [
            [
               'trigger' => TRIGGER_ON_PLAY,
               'effects' => [
                  ['type' => ABILITY_GAIN_RESOURCE, 'value' => 2],
               ],
            ],
            [
               'trigger' => TRIGGER_ACTIVATE_CARD,
               'costs' => [
                  ['type' => COST_EXILE_SELF],
               ],
               'effects' => [
                  ['type' => ABILITY_GAIN_FORCE, 'value' => 1],
               ]
            ]
         ],
      ],
   ];

$this->galaxy_deck_composition = $empire_deck_composition + $rebel_deck_composition + $neutral_deck_composition;

$this->starter_decks = [
   FACTION_REBEL => $rebel_starter_deck,
   FACTION_EMPIRE => $empire_starter_deck
];

$this->base_decks = [
   FACTION_REBEL => $rebel_bases,
   FACTION_EMPIRE => $empire_bases
];


$this->all_bases = $empire_bases + $rebel_bases;
