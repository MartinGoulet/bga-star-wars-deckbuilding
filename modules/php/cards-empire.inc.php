<?php

use Bga\Games\StarWarsDeckbuilding\Targeting\TargetScope;

$empire_cards = [
   // Imperials
   CardIds::TIE_FIGHTER => [
      'name' => clienttranslate('TIE Fighter'),
      'gametext' => clienttranslate("If you have a capital ship in play, draw 1 card"),
      'img' => CardIds::TIE_FIGHTER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_FIGHTER],
      'cost' => 1,
      'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_SELF_SHIP_AREA],
                     'filters' => [
                        ['type' => FILTER_CARD_TYPES, 'cardTypes' => [CARD_TYPE_SHIP]],
                     ],
                  ]
               ]
            ],
            'effects' => [
               ['type' => EFFECT_DRAW_CARD, 'amount' => 1],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_RESOURCE,
            'amount' => 1,
         ]
      ]
   ],

   CardIds::AT_ST => [
      'name' => clienttranslate('AT-ST'),
      'gametext' => clienttranslate('Discard 1 card from the galaxy row'),
      'img' => CardIds::AT_ST,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_VEHICLE],
      'cost' => 4,
      'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                  ],
                  'storeAs' => 'atst_selected_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'destination' => ZONE_GALAXY_DISCARD,
                  'cardRef' => 'atst_selected_card'
               ],
            ],
         ]
      ]
   ],

   CardIds::LANDING_CRAFT => [
      'name' => clienttranslate('Landing Craft'),
      'gametext' => clienttranslate(""),
      'rewardText' => clienttranslate("Gain 4 resources"),
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
                  "type" => EFFECT_CHOICE_OPTION,
                  'options' => [
                     [
                        'label' => clienttranslate('Gain 4 resources'),
                        'type' => EFFECT_GAIN_RESOURCE,
                        'amount' => 4
                     ],
                     [
                        'label' => clienttranslate('Repair 4 damage on base'),
                        'type' => EFFECT_REPAIR_DAMAGE_BASE,
                        'amount' => 4
                     ],
                  ],
               ]
            ],
         ]
      ],
      'rewards' => [
         ['type' => EFFECT_GAIN_RESOURCE, 'amount' => 4]
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
      'traits' => [TRAIT_BOUNTY_HUNTER],
      'unique' => true,
      'cost' => 5,
      'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_DEFEAT_TARGET_GALAXY_ROW,
            'effects' => [
               ['type' => EFFECT_DRAW_CARD, 'amount' => 1],
            ],
         ]
      ]
   ],

   CardIds::IMPERIAL_CARRIER => [
      'name' => clienttranslate('Imperial Carrier'),
      'gametext' => clienttranslate("While Imperial Carrier is in play, each of your *Fighter* units gains 1 attack"),
      'img' => CardIds::IMPERIAL_CARRIER,
      'type' => CARD_TYPE_SHIP,
      'faction' => FACTION_EMPIRE,
      'cost' => 5,
      'stats' => ['power' => 0, 'resource' => 3, 'force' => 0],
      'abilities' => [
         [
            'type' => ABILITY_AURA_ATTACK_MODIFIER,
            'value' => 1,
            'target' => [
               'zones' => [TARGET_SCOPE_SELF_PLAY_AREA],
               'filters' => [
                  ['type' => FILTER_HAS_TRAIT, 'traits' => [TRAIT_FIGHTER]],
               ],
            ],
         ]
      ],
   ],

   CardIds::AT_AT => [
      'name' => clienttranslate('AT-AT'),
      'gametext' => clienttranslate('Add a *Trooper* from your discard pile to your hand'),
      'img' => CardIds::AT_AT,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_VEHICLE],
      'cost' => 6,
      'stats' => ['power' => 6, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_SELF_DISCARD],
                     'filters' => [
                        [
                           'type' => FILTER_HAS_TRAIT,
                           'traits' => [TRAIT_TROOPER],
                        ]
                     ],
                  ],
               ],
            ],
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_SELF_DISCARD],
                     'filters' => [
                        [
                           'type' => FILTER_HAS_TRAIT,
                           'traits' => [TRAIT_TROOPER],
                        ]
                     ],
                  ],
                  'storeAs' => 'atat_selected_trooper',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'cardRef' => 'atat_selected_trooper',
                  'destination' => ZONE_HAND,
               ],
            ],
         ]
      ],
   ],

   CardIds::GRAND_MOFF_TARKIN => [
      'name' => clienttranslate('Grand Moff Tarkin'),
      'gametext' => clienttranslate("Add an Empire card from the galaxy row to your hand. You must exile that card at the end of your turn"),
      'rewardText' => clienttranslate("Gain 3 resources and 3 force"),
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
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_EMPIRE]]
                     ],
                  ],
               ],
            ],
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_EMPIRE]]
                     ],
                  ],
                  'storeAs' => 'tarkin_selected_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'cardRef' => 'tarkin_selected_card',
                  'destination' => ZONE_HAND,
               ],
               [
                  'type' => EFFECT_REGISTER_DELAYED,
                  'trigger' => TRIGGER_END_OF_TURN,
                  'effects' => [
                     [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'cardRef' => 'tarkin_selected_card',
                        'destination' => ZONE_EXILE,
                     ]
                  ]
               ]
            ],
         ],
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
      'abilities' => []
   ],

   CardIds::TIE_BOMBER => [
      'name' => clienttranslate('TIE Bomber'),
      'gametext' => clienttranslate('Discard 1 card from the galaxy row'),
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
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                  ],
                  'storeAs' => 'tie_bomber_selected_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'cardRef' => 'tie_bomber_selected_card',
                  'destination' => ZONE_GALAXY_DISCARD,
               ]
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
      'gametext' => clienttranslate("Reveal the top card of the Galaxy deck. If it's an Empire card, gain 1 force. If it's an enemy card, discard it"),
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
                  'type' => EFFECT_REVEAL_TOP_CARD,
                  'from' => ZONE_GALAXY_DECK,
                  'storeAs' => 'scout_revealed_card'
               ],
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     [
                        'type' => CONDITION_CARD_FACTION_IS,
                        'factions' => [FACTION_EMPIRE],
                        'cardRef' => 'scout_revealed_card',
                     ]
                  ],
                  'effects' => [
                     ['type' => EFFECT_GAIN_FORCE, 'amount' => 1],
                     ['type' => EFFECT_HIDE_CARDS, 'cardRef' => 'scout_revealed_card']
                  ],
               ],
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     [
                        'type' => CONDITION_CARD_FACTION_IS,
                        'factions' => [FACTION_EMPIRE, FACTION_NEUTRAL],
                        'negate' => true,
                        'cardRef' => 'scout_revealed_card',
                     ]
                  ],
                  'effects' => [
                     [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'destination' => ZONE_GALAXY_DISCARD,
                        'cardRef' => 'scout_revealed_card',
                     ]
                  ],
               ],
            ],
         ],
      ],
   ],

   CardIds::DEATH_TROOPER => [
      'name' => clienttranslate('Death Trooper'),
      'gametext' => clienttranslate("While the Force is with you, this unit gains 2 attack"),
      'rewardText' => clienttranslate("Gain 2 Force"),
      'img' => CardIds::DEATH_TROOPER,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_TROOPER],
      'cost' => 3,
      'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
      'abilities' => [
         [
            'type' => ABILITY_STATIC_ATTACK_MODIFIER,
            'value' => 2,
            'condition' => [
               ['type' => CONDITION_FORCE_IS_WITH_YOU],
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

   CardIds::TIE_INTERCEPTOR => [
      'name' => clienttranslate('TIE Interceptor'),
      'gametext' => clienttranslate("Reveal the top card of the galaxy deck. If it is an Empire card, draw 1 card. If it is an enemy card, discard it"),
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
                  'type' => EFFECT_REVEAL_TOP_CARD,
                  'from' => ZONE_GALAXY_DECK,
                  'storeAs' => 'tie_interceptor_revealed_card'
               ],
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     [
                        'type' => CONDITION_CARD_FACTION_IS,
                        'factions' => [FACTION_EMPIRE],
                        'cardRef' => 'tie_interceptor_revealed_card',
                     ]
                  ],
                  'effects' => [
                     ['type' => EFFECT_DRAW_CARD, 'amount' => 1],
                     ['type' => EFFECT_HIDE_CARDS, 'cardRef' => 'tie_interceptor_revealed_card']
                  ],
               ],
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     [
                        'type' => CONDITION_CARD_FACTION_IS,
                        'factions' => [FACTION_EMPIRE, FACTION_NEUTRAL],
                        'negate' => true,
                        'cardRef' => 'tie_interceptor_revealed_card',
                     ]
                  ],
                  'effects' => [
                     [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'destination' => ZONE_GALAXY_DISCARD,
                        'cardRef' => 'tie_interceptor_revealed_card',
                     ]
                  ],
               ],
            ],
         ],
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
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => ['zones' => [TARGET_SCOPE_SELF_HAND]],
               ],
            ],
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_SELF_HAND],
                  ],
                  'storeAs' => 'gozanti_selected_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'cardRef' => 'gozanti_selected_card',
                  'destination' => ZONE_DISCARD,
               ],
               ['type' => EFFECT_DRAW_CARD, 'amount' => 1]
            ],
         ]
      ],
   ],

   CardIds::MOFF_JERJERROD => [
      'name' => clienttranslate('Moff Jerjerrod'),
      'gametext' => clienttranslate("Look at the top card of the galaxy deck. If the Force is with you, you may swap that card with a card from the galaxy row"),
      'rewardText' => clienttranslate("Gain 3 Force"),
      'img' => CardIds::MOFF_JERJERROD,
      'type' => CARD_TYPE_UNIT,
      'faction' => FACTION_EMPIRE,
      'traits' => [TRAIT_OFFICER],
      'unique' => true,
      'cost' => 4,
      'stats' => ['power' => 2, 'resource' => 2, 'force' => 0],
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_DECK],
                  ],
                  'storeAs' => GVAR_GALAXY_DECK_REVEALED_CARD,
               ],
               [
                  'type' => EFFECT_REVEAL_CARDS,
                  'cardRef' => GVAR_GALAXY_DECK_REVEALED_CARD,
               ],
               [
                  'type' => EFFECT_CONDITIONAL,
                  'conditions' => [
                     ['type' => CONDITION_FORCE_IS_WITH_YOU],
                  ],
                  'effects' =>
                  [
                     [
                        'type' => EFFECT_CHOICE_OPTION,
                        'options' => [
                           [
                              'label' => clienttranslate('Keep the card on top of the galaxy deck'),
                              'type' => EFFECT_CONDITIONAL,
                              'effects' => [
                                 [
                                    'type' => EFFECT_HIDE_CARDS,
                                    'cardRef' => GVAR_GALAXY_DECK_REVEALED_CARD,
                                 ],
                              ],
                           ],
                           [
                              'label' => clienttranslate('Swap the card with a card from the galaxy row'),
                              'type' => EFFECT_CONDITIONAL,
                              'effects' => [
                                 [
                                    'type' => EFFECT_SELECT_CARDS,
                                    'target' => [
                                       'zones' => [TARGET_SCOPE_GALAXY_ROW],
                                    ],
                                    'storeAs' => 'moff_swap_target',
                                 ],
                                 [
                                    'type' => EFFECT_MOVE_SELECTED_CARDS,
                                    'destination' => ZONE_GALAXY_ROW,
                                    'cardRef' => GVAR_GALAXY_DECK_REVEALED_CARD,
                                 ],
                                 [
                                    'type' => EFFECT_MOVE_SELECTED_CARDS,
                                    'destination' => ZONE_GALAXY_DECK,
                                    'cardRef' => 'moff_swap_target',
                                 ],
                              ],
                           ],
                        ]
                     ],
                  ]
               ],
            ]
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 3,
         ]
      ]

   ],

   CardIds::GENERAL_VEERS => [
      'name' => clienttranslate('General Veers'),
      'gametext' => clienttranslate("If you have a *Trooper* or *Vehicle* in play, draw 1 card"),
      'rewardText' => clienttranslate("Gain 3 Force"),
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
            'conditions' => [
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_SELF_PLAY_AREA, TARGET_SCOPE_SELF_SHIP_AREA],
                     'filters' => [
                        [
                           'type' => FILTER_HAS_TRAIT,
                           'traits' => [TRAIT_TROOPER, TRAIT_VEHICLE],
                        ]
                     ],
                  ],
               ],
            ],
            'effects' => [
               ['type' => EFFECT_DRAW_CARD, 'amount' => 1],
            ],
         ]
      ],
      'rewards' => [
         [
            'type' => EFFECT_GAIN_FORCE,
            'amount' => 3,
         ]
      ]
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
      'gametext' => clienttranslate("When you reveal Correlia, purchase an Empire card or neutral card from the galaxy row for free and add it to your hand"),
      'img' => 2,
      'faction' => FACTION_EMPIRE,
      'health' => 10,
      'beginner' => true,
      'abilities' => [
         [
            'trigger' => TRIGGER_ON_REVEAL_BASE,
            'effects' => [
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        ['type' => FILTER_FACTIONS, 'factions' => [FACTION_EMPIRE, FACTION_NEUTRAL]],
                     ],
                  ],
                  'storeAs' => 'correlia_selected_card',
               ],
               [
                  'type' => EFFECT_PURCHASE_CARD_FREE,
                  'cardRef' => 'correlia_selected_card',
               ],
               [
                  'type' => EFFECT_MOVE_SELECTED_CARDS,
                  'cardRef' => 'correlia_selected_card',
                  'destination' => ZONE_HAND,
               ]
            ]
         ]
      ],
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
      'gametext' => clienttranslate("Spend 4 resources to destroy a capital ship your opponent has in play or a capital ship in the galaxy row"),
      'img' => 8,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'beginner' => true,
      'abilities' => [
         [
            'trigger' => TRIGGER_ACTIVATE_CARD,
            'conditions' => [
               ['type' => CONDITION_HAS_RESOURCES, 'count' => 4],
               [
                  'type' => CONDITION_HAS_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_OPPONENT_SHIP_AREA, TARGET_SCOPE_GALAXY_ROW],
                     'filters' => [
                        [
                           'type' => FILTER_CARD_TYPES,
                           'cardTypes' => [CARD_TYPE_SHIP],
                        ],
                     ],
                  ],
               ],
            ],
            'effects' => [
               [
                  'type' => EFFECT_PAY_RESOURCE,
                  'amount' => 4,
               ],
               [
                  'type' => EFFECT_SELECT_CARDS,
                  'target' => [
                     'zones' => [TARGET_SCOPE_GALAXY_ROW, TARGET_SCOPE_OPPONENT_SHIP_AREA],
                     'filters' => [
                        [
                           'type' => FILTER_CARD_TYPES,
                           'cardTypes' => [CARD_TYPE_SHIP],
                        ],
                     ],
                  ],
                  'storeAs' => 'death_star_target',
               ],
               [
                  'type' => EFFECT_DESTROY_SELECTED_CARD,
                  'cardRef' => 'death_star_target',
               ]
            ]
         ]
      ],
   ],
   CardIds::ENDOR => [
      'name' => clienttranslate('Endor'),
      'gametext' => clienttranslate("While Endor is your base, each of your *Trooper* and *Vehicle* units gains 1 attack"),
      'img' => 9,
      'faction' => FACTION_EMPIRE,
      'health' => 16,
      'beginner' => true,
      'abilities' => [
         [
            'type' => ABILITY_AURA_ATTACK_MODIFIER,
            'value' => 1,
            'target' => [
               'zones' => [TARGET_SCOPE_SELF_PLAY_AREA, TARGET_SCOPE_SELF_SHIP_AREA],
               'filters' => [
                  [
                     'type' => FILTER_HAS_TRAIT,
                     'traits' => [TRAIT_TROOPER, TRAIT_VEHICLE],
                  ]
               ],
            ],
         ]
      ],
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
