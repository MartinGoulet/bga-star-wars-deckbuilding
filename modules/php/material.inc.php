<?php

require_once('cards-empire.inc.php');
require_once('cards-rebel.inc.php');
require_once('cards-neutral.inc.php');

$this->card_types = $empire_cards + $rebel_cards + $neutral_cards +
   [
      // Generic
      CardIds::OUTER_RIM_PILOT => [
         'name' => clienttranslate('Outer Rim Pilot'),
         'gametext' => clienttranslate("Exile this unit to gain 1 Force."),
         'img' => CardIds::OUTER_RIM_PILOT,
         'type' => CARD_TYPE_UNIT,
         'faction' => FACTION_NEUTRAL,
         'cost' => 2,
         'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
         'abilities' => [
            [
               'trigger' => TRIGGER_ACTIVATE_CARD,
               'effects' => [
                  [
                     'type' => EFFECT_SELECT_CURRENT_CARD,
                     'storeAs' => 'outer_rim',
                  ],
                  [
                     'type' => EFFECT_MOVE_SELECTED_CARDS,
                     'destination' => ZONE_EXILE,
                     'cardRef' => 'outer_rim',
                  ],
                  [
                     'type' => EFFECT_GAIN_FORCE,
                     'amount' => 1,
                  ],
               ],
            ],
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

$this->solo_progress_track = [
   FACTION_REBEL => [
      2 => SOLO_GAIN_FORCE, 
      4 => SOLO_GAIN_SHUTTLE,
      5 => SOLO_GAIN_FORCE,
      6 => SOLO_GAIN_TEMPLE_GUARDIAN,
      7 => SOLO_GAIN_FORCE,
      11 => SOLO_GAIN_FORCE,
      12 => SOLO_GAIN_LEADER,
   ],
   FACTION_EMPIRE => [
      2 => SOLO_GAIN_FORCE, 
      4 => SOLO_GAIN_FORCE,
      5 => SOLO_GAIN_INQUISITOR,
      6 => SOLO_GAIN_SHUTTLE,
      7 => SOLO_GAIN_FORCE,
      12 => SOLO_GAIN_LEADER,
   ]
];