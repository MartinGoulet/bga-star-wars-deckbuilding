<?php

$rebel_cards = [
   CardIds::Y_WING => [
      'name' => clienttranslate('Y-Wing'),
      'img' => CardIds::Y_WING,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 1,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::JYN_ERSO => [
      'name' => clienttranslate('Jyn Erso'),
      'img' => CardIds::JYN_ERSO,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 4,
      'unique' => true,
      'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::U_WING => [
      'name' => clienttranslate('U-Wing'),
      'gametext' => clienttranslate('If the Force is with you, repair 3 damage from your base'),
      'rewardText' => clienttranslate('Gain 4 Resources'),
      'img' => CardIds::U_WING,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 4,
      'stats' => ['power' => 0, 'resource' => 3, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               ['type' => CONDITION_FORCE_IS_WITH_YOU],
            ],
            'effects' => [
               [
                  'type' => EFFECT_REPAIR_DAMAGE_BASE,
                  'amount' => 3,
               ]
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_RESOURCE,
            'amount' => 4,
         ]
      ]
   ],

   CardIds::HAMMERHEAD_CORVETTE => [
      'name' => clienttranslate('Hammerhead Corvette'),
      'gametext' => clienttranslate('Exile this capital ship to destroy a capital ship your opponent has in play, or an ennemy capital ship in the galaxy row'),
      'img' => CardIds::HAMMERHEAD_CORVETTE,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_REBEL,
      'cost' => 4,
      'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_OPPONENT_SHIP_AREA, TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_CARD_TYPES, 'cardTypes' => [CARD_TYPE_SHIP]],
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_REBEL, FACTION_NEUTRAL], 'negate' => true],
                     ]
                  ],
                  'storeAs' => 'hammerhead_target',
               ],
               [
                  'type' => EFFECT_DESTROY_SELECTED_CARD,
                  'cardRef' => 'hammerhead_target',
               ],
               [
                  'type' => EFFECT_SELECT_CURRENT_CARD,
                  'storeAs' => 'hammerhead_self',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'destination' => ZONE_EXILE,
                  'cardRef' => 'hammerhead_self',
               ],
            ],
         ]
      ],
   ],

   CardIds::HAN_SOLO => [
      'name' => clienttranslate('Han Solo'),
      'img' => CardIds::HAN_SOLO,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 5,
      'unique' => true,
      'stats' => ['power' => 3, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_DRAW_CARD,
                  'amount' => 1,
               ],
               [
                  'type' => EFFECT_DRAW_CARD,
                  'amount' => 1,
                  'conditions' => [
                     [
                        'type' => CONDITION_CARD_IN_PLAY,
                        'cardIds' => [CardIds::MILLENNIUM_FALCON],
                     ],
                  ],
               ],
            ],
         ]
      ]
   ],

   CardIds::CASSIAN_ANDOR => [
      'name' => clienttranslate('Cassian Andor'),
      'img' => CardIds::CASSIAN_ANDOR,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 5,
      'unique' => true,
      'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
      'abilities' => [],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_RESOURCE,
            'amount' => 3,
         ],
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 2,
         ]
      ]
   ],

   CardIds::B_WING => [
      'name' => clienttranslate('B-Wing'),
      'img' => CardIds::B_WING,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 5,
      'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_CHOICE_OPTION,
                  'target' => TARGET_OPPONENT,
                  'options' => [
                     [
                        'label' => clienttranslate('Discard a card'),
                        'type' => EFFECT_CONDITIONAL,
                        'conditions' => [],
                        'effects' => [
                           [
                              'type' => EFFECT_SELECT_CARDS,
                              'target' => [
                                 'zones' => [TARGET_SCOPE_OPPONENT_HAND],
                                 'selectionMode' => SELECTION_MODE_OPPONENT_CHOICE,
                              ],
                              'storeAs' => 'b_wing_discard_card',
                           ],
                           [
                              'type' => EFFECT_MOVE_SELECTED_CARDS,
                              'destination' => ZONE_PLAYER_DISCARD,
                              'target' => TARGET_OPPONENT,
                              'cardRef' => 'b_wing_discard_card',
                           ],
                        ]
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Force'),
                        'type' => EFFECT_GAIN_FORCE,
                        'amount' => 1,
                     ],
                  ],
               ],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_EXILE_CARD,
            'target' => TARGET_SELF,
            'zones' => [ZONE_DISCARD, ZONE_HAND],
            'count' => 2,
         ]
      ]
   ],

   CardIds::PRINCESS_LEIA => [
      'name' => clienttranslate('Princess Leia'),
      'gametext' => clienttranslate("Purchase a Rebel card from the galaxy row for free. If the Force is with you, place the card on top of your deck"),
      'rewardText' => clienttranslate("Gain 3 resources and 3 force"),
      'img' => CardIds::PRINCESS_LEIA,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 6,
      'unique' => true,
      'stats' => ['power' => 2, 'resource' => 2, 'force' => 2],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_REBEL]],
                     ],
                  ]
               ],
            ],
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_REBEL]],
                     ],
                  ],
                  'storeAs' => 'leia_galaxy_card',
               ],
               [
                  'type' => EFFECT_PURCHASE_CARD_FREE,
                  'cardRef' => 'leia_galaxy_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'conditions' => [
                     ['type' => CONDITION_FORCE_IS_WITH_YOU],
                  ],
                  'cardRef' => 'leia_galaxy_card',
                  'destination' => ZONE_PLAYER_DECK,
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'conditions' => [
                     ['type' => CONDITION_FORCE_IS_NOT_WITH_YOU],
                  ],
                  'cardRef' => 'leia_galaxy_card',
                  'destination' => ZONE_PLAYER_DISCARD,
               ]
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_RESOURCE,
            'amount' => 3,
         ],
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 3,
         ]
      ]
   ],

   CardIds::MON_CALAMARI_CRUISER => [
      'name' => clienttranslate('Mon Calamari Cruiser'),
      'img' => CardIds::MON_CALAMARI_CRUISER,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_REBEL,
      'cost' => 6,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::MILLENNIUM_FALCON => [
      'name' => clienttranslate('Millennium Falcon'),
      'gametext' => clienttranslate('Add a unique unit from your discard pile to your hand'),
      'rewardText' => clienttranslate('Purchase a card of your faction for free'),
      'img' => CardIds::MILLENNIUM_FALCON,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 7,
      'unique' => true,
      'stats' => ['power' => 5, 'resource' => 2, 'force' => 0],
      'abilities' => [],
      'rewards' => [
         [
            'type' => EFFECT_CONDITIONAL,
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_REBEL, FACTION_NEUTRAL], 'negate' => true],
                     ],
                  ]
               ]
            ],
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_REBEL, FACTION_NEUTRAL], 'negate' => true],
                     ],
                  ],
                  'storeAs' => 'falcon_retrieve_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'destination' => ZONE_HAND,
                  'cardRef' => 'falcon_retrieve_card',
               ],
               [
                  'type' => EFFECT_PURCHASE_CARD_FREE,
                  'cardRef' => 'falcon_retrieve_card',
               ],
            ],
         ]
      ]
   ],

   CardIds::LUKE_SKYWALKER => [
      'name' => clienttranslate('Luke Skywalker'),
      'img' => CardIds::LUKE_SKYWALKER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 8,
      'unique' => true,
      'stats' => ['power' => 6, 'resource' => 0, 'force' => 2],
      'abilities' => []
   ],

   CardIds::BAZE_MALBUS => [
      'name' => clienttranslate('Baze Malbus'),
      'img' => CardIds::BAZE_MALBUS,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 2,
      'unique' => true,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => []
   ],

   CardIds::SNOWSPEEDER => [
      'name' => clienttranslate('Snowspeeder'),
      'gametext' => clienttranslate('Your opponent discards 1 card from their hand'),
      'rewardText' => clienttranslate('Exile 1 card from your hand or discard pile'),
      'img' => CardIds::SNOWSPEEDER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 2,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [],
      'rewards' => [
         [
            'type' => EFFECT_SELECT_CARDS,
            'target' => [
               'zones' => [TARGET_SCOPE_SELF_HAND, TARGET_SCOPE_SELF_DISCARD],
               'min' => 1,
            ],
            'storeAs' => 'snowspeeder_exile_card',
         ],
         [
            'type' => EFFECT_MOVE_SELECTED_CARDS,
            'destination' => ZONE_EXILE,
            'cardRef' => 'snowspeeder_exile_card',
         ]
      ]
   ],

   CardIds::DUROS_SPY => [
      'name' => clienttranslate('Duros Spy'),
      'gametext' => clienttranslate("Your opponent must choose: Either they discard 1 card from their hand, or you gain 1 force"),
      'rewardText' => clienttranslate('Exile 1 card from your hand or discard pile'),
      'img' => CardIds::DUROS_SPY,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 2,
      'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_CHOICE_OPTION,
                  'target' => TARGET_OPPONENT,
                  'options' => [
                     [
                        'type' => EFFECT_CONDITIONAL,
                        'label' => clienttranslate('Discard a card from hand'),
                        'conditions' => [],
                        'effects' => [
                           [
                              'type' => EFFECT_SELECT_CARDS,
                              'target' => [
                                 'zones' => [TARGET_SCOPE_OPPONENT_HAND],
                                 'selectionMode' => SELECTION_MODE_OPPONENT_CHOICE,
                              ],
                              'storeAs' => 'duros_spy_discard_card',
                           ],
                           [
                              'type' => EFFECT_MOVE_SELECTED_CARDS,
                              'destination' => ZONE_PLAYER_DISCARD,
                              'target' => TARGET_OPPONENT,
                              'cardRef' => 'duros_spy_discard_card',
                           ]
                        ]
                     ],
                     [
                        'type' => EFFECT_GAIN_FORCE,
                        'target' => TARGET_SELF,
                        'amount' => 1,
                        'label' => clienttranslate('Opponent gains 1 Force'),
                     ],
                  ],

               ],
            ],
         ]
      ],
   ],

   CardIds::REBEL_TRANSPORT => [
      'name' => clienttranslate('Rebel Transport'),
      'img' => CardIds::REBEL_TRANSPORT,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_REBEL,
      'cost' => 2,
      'stats' => ['power' => 0, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            "trigger" => TRIGGER_ACTIVATE_CARD,
            "effects" => [
               [
                  "type" => EFFECT_CHOICE,
                  "options" => [
                     [
                        'label' => clienttranslate('Repair 2 damage from your Base '),
                        'type' => EFFECT_REPAIR_DAMAGE_BASE,
                        'amount' => 2
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Resource'),
                        'type' => EFFECT_GAIN_RESOURCE,
                        'amount' => 1
                     ],
                  ],
               ]
            ],
         ]
      ],
   ],

   CardIds::CHIRRUT_IMWE => [
      'name' => clienttranslate('Chirrut Îmwe'),
      'img' => CardIds::CHIRRUT_IMWE,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 3,
      'unique' => true,
      'stats' => ['power' => 0, 'resource' => 0, 'force' => 2],
      'abilities' => [],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 2,
         ]
      ]
   ],

   CardIds::REBEL_COMMANDO => [
      'name' => clienttranslate('Rebel Commando'),
      'gametext' => clienttranslate('Your opponent discards 1 card from their hand (at random if the Force is with you)'),
      'rewardText' => clienttranslate('Gain 2 Force'),
      'img' => CardIds::REBEL_COMMANDO,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 3,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               // Force WITH you → random discard
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     ['type' => CONDITION_FORCE_IS_WITH_YOU],
                  ],
                  'effects' => [
                     [
                        'type' => EFFECT_SELECT_CARDS,
                        'target' => [
                           'zones' => [TARGET_SCOPE_OPPONENT_HAND],
                           'selectionMode' => SELECTION_MODE_RANDOM,
                        ],
                        'storeAs' => 'rebel_commando_card',
                     ],
                     [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'destination' => ZONE_DISCARD,
                        'target' => TARGET_OPPONENT,
                        'cardRef' => 'rebel_commando_card',
                     ],
                  ],
               ],

               // Force NOT with you → opponent chooses
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     ['type' => CONDITION_FORCE_IS_NOT_WITH_YOU],
                  ],
                  'effects' => [
                     [
                        'type' => EFFECT_SELECT_CARDS,
                        'target' => [
                           'zones' => [TARGET_SCOPE_OPPONENT_HAND],
                           'selectionMode' => SELECTION_MODE_OPPONENT_CHOICE,
                        ],
                        'storeAs' => 'rebel_commando_card',
                     ],
                     [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'destination' => ZONE_DISCARD,
                        'target' => TARGET_OPPONENT,
                        'cardRef' => 'rebel_commando_card',
                     ],
                  ],
               ],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 2,
         ]
      ]
   ],

   CardIds::X_WING => [
      'name' => clienttranslate('X-Wing'),
      'img' => CardIds::X_WING,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 3,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               ['type' => CONDITION_FORCE_IS_WITH_YOU],
               ['type' => CONDITION_HAS_DAMAGE_ON_BASE]
            ],
            'effects' => [
               [
                  'type' => EFFECT_REPAIR_DAMAGE_BASE,
                  'amount' => 3,
               ]
            ]
         ]
      ],
   ],

   // Rebels
   CardIds::CHEWBACCA => [
      'name' => clienttranslate('Chewbacca'),
      'img' => CardIds::CHEWBACCA,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'unique' => true,
      'cost' => 4,
      'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               ['type' => CONDITION_ANOTHER_UNIQUE_UNIT_IN_PLAY],
            ],
            'effects' => [
               ['type' => ABILITY_DRAW_CARD, 'value' => 1],
            ],
         ]
      ]
   ],

   // Starter Rebel
   CardIds::ALLIANCE_SHUTTLE => [
      'name' => clienttranslate('Alliance Shuttle'),
      'img' => CardIds::ALLIANCE_SHUTTLE,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 0,
      'stats' => ['power' => 0, 'resource' => 1, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::REBEL_TROOPER => [
      'name' => clienttranslate('Rebel Trooper'),
      'img' => CardIds::REBEL_TROOPER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
      'cost' => 0,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [],
   ],

   CardIds::TEMPLE_GUARDIAN => [
      'name' => clienttranslate('Temple Guardian'),
      'img' => CardIds::TEMPLE_GUARDIAN,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_REBEL,
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
                        'amount' => 1
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Resource'),
                        'type' => EFFECT_GAIN_RESOURCE,
                        'amount' => 1
                     ],
                     [
                        'label' => clienttranslate('Gain 1 Force'),
                        'type' => EFFECT_GAIN_FORCE,
                        'amount' => 1
                     ],
                  ],
               ]
            ],
         ],
      ],
   ],
];

$rebel_bases = [
   CardIds::DANTOOINE => [
      'name' => clienttranslate('Dantooine'),
      'img' => 1,
      'faction' => FACTION_REBEL,
      'health' => 8,
      'beginner' => true,
      'starting_base' => true,
      'abilities' => [],
   ],
   CardIds::MON_CALA => [
      'name' => clienttranslate('Mon Cala'),
      'img' => 2,
      'faction' => FACTION_REBEL,
      'health' => 10,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::DAGOBAH => [
      'name' => clienttranslate('Dagobah'),
      'img' => 3,
      'faction' => FACTION_REBEL,
      'health' => 12,
      'abilities' => [],
   ],
   CardIds::BESPIN => [
      'name' => clienttranslate('Bespin'),
      'img' => 4,
      'faction' => FACTION_REBEL,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::ALDERAAN => [
      'name' => clienttranslate('Alderaan'),
      'img' => 5,
      'faction' => FACTION_REBEL,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::HOTH => [
      'name' => clienttranslate('Hoth'),
      'img' => 6,
      'faction' => FACTION_REBEL,
      'health' => 14,
      'beginner' => true,
      'abilities' => [],
   ],
   CardIds::JEDHA => [
      'name' => clienttranslate('Jedha'),
      'img' => 7,
      'faction' => FACTION_REBEL,
      'health' => 14,
      'abilities' => [],
   ],
   CardIds::TATOOINE => [
      'name' => clienttranslate('Tatooine'),
      'img' => 8,
      'faction' => FACTION_REBEL,
      'health' => 16,
      'abilities' => [],
   ],
   CardIds::SULLUST => [
      'name' => clienttranslate('Sullust'),
      'img' => 9,
      'faction' => FACTION_REBEL,
      'health' => 16,
      'beginner' => true,
      'abilities' => [
         [
            'trigger' => TRIGGER_WHEN_PURCHASED,
            'effects' => [
               [
                  'type' => EFFECT_MOVE_CARD,
                  'target' => TARGET_SELF,
                  'destination' => ZONE_TOP_DECK,
                  'conditions' => [
                     ['type' => CONDITION_FIRST_PURCHASE_THIS_TURN]
                  ]
               ]
            ]
         ]
      ],
   ],
   CardIds::YAVIN_4 => [
      'name' => clienttranslate('Yavin 4'),
      'gametext' => clienttranslate("When your opponent discards a card from their hand during your turn, deal 2 damage to their base"),
      'img' => 10,
      'faction' => FACTION_REBEL,
      'health' => 16,
      'beginner' => true,
      'abilities' => [
      ],
   ],
];

$rebel_deck_composition = [
   CardIds::Y_WING => 2,
   CardIds::JYN_ERSO => 1,
   CardIds::U_WING => 2,
   CardIds::HAMMERHEAD_CORVETTE => 2,
   CardIds::HAN_SOLO => 1,
   CardIds::CASSIAN_ANDOR => 1,
   CardIds::B_WING => 2,
   CardIds::PRINCESS_LEIA => 1,
   CardIds::MON_CALAMARI_CRUISER => 2,
   CardIds::MILLENNIUM_FALCON => 1,
   CardIds::LUKE_SKYWALKER => 1,
   CardIds::BAZE_MALBUS => 1,
   CardIds::SNOWSPEEDER => 2,
   CardIds::DUROS_SPY => 2,
   CardIds::REBEL_TRANSPORT => 2,
   CardIds::CHIRRUT_IMWE => 1,
   CardIds::REBEL_COMMANDO => 2,
   CardIds::X_WING => 3,
   CardIds::CHEWBACCA => 1,
];

$rebel_starter_deck = [
   CardIds::ALLIANCE_SHUTTLE => 7,
   CardIds::REBEL_TROOPER => 2,
   CardIds::TEMPLE_GUARDIAN => 1
];
