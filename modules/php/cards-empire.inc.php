<?php

$empire_cards = [
   // Imperials
   CardIds::TIE_FIGHTER => [
      'name' => clienttranslate('TIE Fighter'),
      'img' => CardIds::TIE_FIGHTER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_FIGHTER],
      'cost' => 1,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'condition' => [
               ['type' => CONDITION_CAPITAL_STARSHIP_IN_PLAY]
            ],
            'effects' => [
               ['type' => ABILITY_DRAW_CARD, 'value' => 1],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_RESOURCE,
            'count' => 1,
         ]
      ]
   ],

   CardIds::AT_ST => [
      'name' => clienttranslate('AT-ST'),
      'img' => CardIds::AT_ST,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_VEHICLE],
      'cost' => 4,
      'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'condition' => [],
            'effects' => [
               ['type' => ABILITY_DISCARD_CARD_GALAXY_ROW, 'value' => 1],
            ],
         ]
      ]
   ],

   CardIds::LANDING_CRAFT => [
      'name' => clienttranslate('Landing Craft'),
      'img' => CardIds::LANDING_CRAFT,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TRANSPORT],
      'cost' => 4,
      'stats' => ['power' => 0, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  "type" => ABILITY_CHOICE,
                  'options' => [
                     ['type' => CHOICE_OPTION_GAIN_RESOURCE, 'value' => 4],
                     ['type' => CHOICE_OPTION_REPAIR_DAMAGE_BASE, 'value' => 4],
                  ],
               ]
            ],
         ]
      ]
   ],

   CardIds::DIRECTOR_KRENNIC => [
      'name' => clienttranslate('Director Krennic'),
      'img' => CardIds::DIRECTOR_KRENNIC,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 5,
      'stats' => ['power' => 3, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => ABILITY_DRAW_CARD,
                  'value' => 1,
                  'conditional_override' => [
                     'condition' => [
                        ['type' => CONDITION_BASE_IS_DEATH_STAR],
                     ],
                     'value' => 2,
                  ],
               ],
            ],
         ]
      ]
   ],

   CardIds::BOBA_FETT => [
      'name' => clienttranslate('Boba Fett'),
      'img' => CardIds::BOBA_FETT,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_BOUNTER_HUNTER],
      'unique' => true,
      'cost' => 5,
      'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_DEFEAT_TARGET_GALAXY_ROW,
            'effects' => [
               ['type' => ABILITY_DRAW_CARD, 'value' => 1],
            ],
         ]
      ]
   ],

   CardIds::IMPERIAL_CARRIER => [
      'name' => clienttranslate('Imperial Carrier'),
      'img' => CardIds::IMPERIAL_CARRIER,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_EMPIRE,
      'cost' => 5,
      'stats' => ['power' => 0, 'resource' => 3, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_WHILE_IN_PLAY,
            'effects' => [
               [
                  'type' => EFFECT_MODIFY_ATTACK,
                  'value' => 1,
                  'target' => [
                     'scope' => TARGET_YOUR_UNITS,
                     'filter' => [
                        [
                           'type' => FILTER_HAS_TRAIT,
                           'traits' => TRAIT_FIGHTER
                        ]
                     ],
                  ],
               ],
            ]
         ]
      ],
   ],

   CardIds::AT_AT => [
      'name' => clienttranslate('AT-AT'),
      'img' => CardIds::AT_AT,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_VEHICLE],
      'cost' => 6,
      'stats' => ['power' => 6, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_MOVE_CARD,
                  'from' => ZONE_DISCARD,
                  'to' => ZONE_HAND,
                  'target' => [
                     'scope' => TARGET_YOUR_CARDS,
                     'filter' => [
                        [
                           'type' => FILTER_HAS_TRAIT,
                           'traits' => TRAIT_TROOPER
                        ]
                     ],
                     'count' => 1,
                     'selection' => SELECTION_PLAYER_CHOICE,
                  ]
               ],
            ],
         ]
      ],
   ],

   CardIds::GRAND_MOFF_TARKIN => [
      'name' => clienttranslate('Grand Moff Tarkin'),
      'img' => CardIds::GRAND_MOFF_TARKIN,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 6,
      'stats' => ['power' => 2, 'resource' => 2, 'force' => 2],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  "type" => EFFECT_MOVE_CARD,
                  "from" => ZONE_GALAXY_ROW,
                  "to" => ZONE_HAND,
                  "target" => [
                     'scope' => TARGET_ANY_CARD,
                     'filter' => [
                        [
                           'type' => FILTER_FACTION,
                           'faction' => FACTION_EMPIRE
                        ]
                     ],
                     'count' => 1,
                     'selection' => SELECTION_PLAYER_CHOICE,
                  ],
                  'store_as' => 'tarking_taken_card',
               ]
            ],
         ],
         [
            'trigger' => TRIGGER_END_OF_TURN,
            'effects' => [
               [
                  'type' => EFFECT_EXILE_CARD,
                  'target' => [
                     'reference' => 'tarking_taken_card',
                  ],
               ]
            ],
         ]
      ],
   ],

   CardIds::STAR_DESTROYER => [
      'name' => clienttranslate('Star Destroyer'),
      'img' => CardIds::STAR_DESTROYER,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_EMPIRE,
      'cost' => 7,
      'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::DARTH_VADER => [
      'name' => clienttranslate('Darth Vader'),
      'img' => CardIds::DARTH_VADER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_SITH],
      'unique' => true,
      'cost' => 8,
      'stats' => ['power' => 6, 'resource' => 0, 'force' => 2],
      'abilities' => [
         [
            'trigger' => TRIGGER_WHILE_IN_PLAY,
            'condition' => [
               ['type' => CONDITION_FORCE_IS_WITH_YOU],
            ],
            'effects' => [
               [
                  'type' => EFFECT_MODIFY_ATTACK,
                  'value' => 4,
                  'target' => [
                     'reference' => 'self',
                  ],
               ],
            ],
         ]
      ]
   ],

   CardIds::ADMIRAL_PIETT => [
      'name' => clienttranslate('Admiral Piett'),
      'img' => CardIds::ADMIRAL_PIETT,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 2,
      'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_WHILE_IN_PLAY,
            'effects' => [
               [
                  'type' => EFFECT_MODIFY_ATTACK,
                  'value' => 1,
                  'target' => [
                     'scope' => TARGET_YOUR_SHIPS,
                  ],
               ],
            ]
         ]
      ]
   ],

   CardIds::TIE_BOMBER => [
      'name' => clienttranslate('TIE Bomber'),
      'img' => CardIds::TIE_BOMBER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_FIGHTER],
      'cost' => 2,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               ['type' => ABILITY_DISCARD_CARD_GALAXY_ROW, 'value' => 1],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_EXILE_CARD,
            'target' => TARGET_SELF,
            'zones' => [ZONE_DISCARD, ZONE_HAND],
            'count' => 1,
         ]
      ]
   ],

   CardIds::SCOUT_TROOPER => [
      'name' => clienttranslate('Scout Trooper'),
      'img' => CardIds::SCOUT_TROOPER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TROOPER],
      'cost' => 2,
      'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => ABILITY_REVEAL_GALAXY_ROW_CARD,
                  'storeAs' => 'revealedCard'
               ],
            ],
         ],
         [
            'trigger' => TRIGGER_CONDITIONAL_EFFECT,
            'condition' => [
               'type' => CONDITION_HAS_TRAIT,
               'card' => 'revealedCard',
               'traits' => TRAIT_EMPIRE,
            ],
            'effects' => [
               'type' => ABILITY_GAIN_FORCE,
               'value' => 1,
            ],
         ],
         [
            'trigger' => TRIGGER_CONDITIONAL_EFFECT,
            'condition' => [
               'type' => CONDITION_IS_ENEMY_CARD,
               'card' => 'revealedCard',
            ],
            'effects' => [
               'type' => EFFECT_DISCARD_CARD,
               'card' => 'revealedCard',
            ],
         ]
      ],
   ],

   CardIds::DEATH_TROOPER => [
      'name' => clienttranslate('Death Trooper'),
      'img' => CardIds::DEATH_TROOPER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TROOPER],
      'cost' => 3,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'condition' => [
               ['type' => CONDITION_FORCE_IS_WITH_YOU],
            ],
            'effects' => [
               ['type' => ABILITY_GAIN_ATTACK, 'value' => 2],
            ],
         ]
      ],
   ],

   CardIds::TIE_INTERCEPTOR => [
      'name' => clienttranslate('TIE Interceptor'),
      'img' => CardIds::TIE_INTERCEPTOR,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_FIGHTER],
      'cost' => 3,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => ABILITY_REVEAL_GALAXY_ROW_CARD,
                  'storeAs' => 'revealedCard'
               ],
            ],
         ],
         [
            'trigger' => TRIGGER_CONDITIONAL_EFFECT,
            'condition' => [
               'type' => CONDITION_HAS_TRAIT,
               'card' => 'revealedCard',
               'traits' => TRAIT_EMPIRE,
            ],
            'effects' => [
               'type' => ABILITY_DRAW_CARD,
               'value' => 1,
            ],
         ],
         [
            'trigger' => TRIGGER_CONDITIONAL_EFFECT,
            'condition' => [
               'type' => CONDITION_IS_ENEMY_CARD,
               'card' => 'revealedCard',
            ],
            'effects' => [
               'type' => EFFECT_DISCARD_CARD,
               'card' => 'revealedCard',
            ],
         ]
      ],
   ],

   CardIds::GOZANTI_CRUISER => [
      'name' => clienttranslate('Gozanti Cruiser'),
      'img' => CardIds::GOZANTI_CRUISER,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_EMPIRE,
      'cost' => 3,
      'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'cost' => [
               'type' => COST_DISCARD_FROM_HAND,
               'amount' => 1,
            ],
            'effects' => [
               [
                  'type' => ABILITY_DRAW_CARD,
                  'amount' => 1,
               ],
            ],
         ]
      ],
   ],

   CardIds::MOFF_JERJERROD => [
      'name' => clienttranslate('Moff Jerjerrod'),
      'img' => CardIds::MOFF_JERJERROD,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 4,
      'stats' => ['power' => 1, 'resource' => 3, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => ABILITY_LOOK_AT_TOP_CARD,
                  'target' => ZONE_GALAXY_DECK,
                  'count' => 1,
               ],
               [
                  'type' => ABILITY_SWAP_TOP_DECK_WITH_ROW,
                  'deck' => ZONE_GALAXY_DECK,
                  'row' => ZONE_GALAXY_ROW,
               ]
            ]
         ]
      ]
   ],

   CardIds::GENERAL_VEERS => [
      'name' => clienttranslate('General Veers'),
      'img' => CardIds::GENERAL_VEERS,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 4,
      'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_DRAW_CARD,
                  'conditions' => [
                     [
                        'type' => CONDITION_HAS_UNIT_IN_PLAY_WITH_TRAIT,
                        'traits' => [TRAIT_TROOPER, TRAIT_VEHICLE],
                        'operator' => CONDITION_OPERATOR_OR
                     ],
                  ],
                  'value' => 1
               ],
            ],
         ]
      ],
   ],

   // Starter Empire
   CardIds::IMPERIAL_SHUTTLE => [
      'name' => clienttranslate('Imperial Shuttle'),
      'img' => CardIds::IMPERIAL_SHUTTLE,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TRANSPORT],
      'cost' => 0,
      'stats' => ['power' => 0, 'resource' => 1, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::STORMTROOPER => [
      'name' => clienttranslate('Stormtrooper'),
      'img' => CardIds::STORMTROOPER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TROOPER],
      'cost' => 0,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::INQUISITOR => [
      'name' => clienttranslate('Inquisitor'),
      'img' => CardIds::INQUISITOR,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'cost' => 0,
      'stats' => ['power' => 0, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            "trigger" => TRIGGER_ON_PLAY,
            "effects" => [
               [
                  "type" => EFFECT_CHOICE,
                  'options' => [
                     [
                        'label' => clienttranslate('Gain 1 Attack'),
                        'type' => EFFECT_GAIN_ATTACK,
                        'count' => 1
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Resource'),
                        'type' => EFFECT_GAIN_RESOURCE,
                        'count' => 1
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Force'),
                        'type' => EFFECT_GAIN_FORCE,
                        'count' => 1
                     ],
                  ],
               ]
            ],
         ],
      ],
   ],
];

$empire_bases = [
   CardIds::LOTHAL => [
      'name' => clienttranslate('Lothal'),
      'img' => 1,
      'faction' => FACTION_EMPIRE,
      'health' => 8,
      'beginner' => true,
      'starting_base' => true,
      'abilities' => [],
   ],
   CardIds::CORRELIA => [
      'name' => clienttranslate('Correlia'),
      'img' => 2,
      'faction' => FACTION_EMPIRE,
      'health' => 10,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::KESSEL => [
      'name' => clienttranslate('Kessel'),
      'img' => 3,
      'faction' => FACTION_EMPIRE,
      'health' => 12,
      'abilities' => [],
   ],
   CardIds::KAFRENE => [
      'name' => clienttranslate('Kafrene'),
      'img' => 5,
      'faction' => FACTION_EMPIRE,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::MUSTAFAR => [
      'name' => clienttranslate('Mustafar'),
      'img' => 4,
      'faction' => FACTION_EMPIRE,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::ORD_MANTELL => [
      'name' => clienttranslate('Ord Mantell'),
      'img' => 6,
      'faction' => FACTION_EMPIRE,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::CORUSCANT => [
      'name' => clienttranslate('Coruscant'),
      'img' => 7,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::DEATH_STAR => [
      'name' => clienttranslate('Death Star'),
      'img' => 8,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::ENDOR => [
      'name' => clienttranslate('Endor'),
      'img' => 9,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::RODIA => [
      'name' => clienttranslate('Rodia'),
      'img' => 10,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'abilities' => [],
   ],
];

$empire_deck_composition = [
   CardIds::TIE_FIGHTER => 3,
   CardIds::ADMIRAL_PIETT => 1,
   CardIds::TIE_BOMBER => 2,
   CardIds::SCOUT_TROOPER => 2,
   CardIds::DEATH_TROOPER => 2,
   CardIds::TIE_INTERCEPTOR => 2,
   CardIds::GOZANTI_CRUISER => 3,
   CardIds::MOFF_JERJERROD => 1,
   CardIds::GENERAL_VEERS => 1,
   CardIds::AT_ST => 2,
   CardIds::LANDING_CRAFT => 2,
   CardIds::DIRECTOR_KRENNIC => 1,
   CardIds::BOBA_FETT => 1,
   CardIds::IMPERIAL_CARRIER => 2,
   CardIds::AT_AT => 1,
   CardIds::GRAND_MOFF_TARKIN => 1,
   CardIds::STAR_DESTROYER => 2,
   CardIds::DARTH_VADER => 1,
];

$empire_starter_deck = [
   CardIds::IMPERIAL_SHUTTLE => 7,
   CardIds::STORMTROOPER => 2,
   CardIds::INQUISITOR => 1,
];
