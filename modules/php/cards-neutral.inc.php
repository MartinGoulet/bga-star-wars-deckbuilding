<?php

$neutral_cards = [
    CardIds::Z95_HEADHUNTER => [
        'name' => clienttranslate('Z-95 Headhunter'),
        'img' => CardIds::Z95_HEADHUNTER,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 1,
        'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::DENGAR => [
        'name' => clienttranslate('Dengar'),
        'img' => CardIds::DENGAR,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 4,
        'unique' => true,
        'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
        'abilities' => []
    ],

    CardIds::QUARREN_MERCENARY => [
        'name' => clienttranslate('Quarren Mercenary'),
        'img' => CardIds::QUARREN_MERCENARY,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 4,
        'stats' => ['power' => 4, 'resource' => 0, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::HWK_290 => [
        'name' => clienttranslate('HWK-290'),
        'img' => CardIds::HWK_290,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 4,
        'stats' => ['power' => 0, 'resource' => 4, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::BLOCKADE_RUNNER => [
        'name' => clienttranslate('Blockade Runner'),
        'img' => CardIds::BLOCKADE_RUNNER,
        'type' => CARD_TYPE_SHIP,
        'faction' => FACTION_NEUTRAL,
        'cost' => 4,
        'stats' => ['power' => 1, 'resource' => 1, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::IG_88 => [
        'name' => clienttranslate('IG-88'),
        'img' => CardIds::IG_88,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 5,
        'unique' => true,
        'stats' => ['power' => 5, 'resource' => 0, 'force' => 0],
        'abilities' => []
    ],

    CardIds::NEBULON_B_FRIGATE => [
        'name' => clienttranslate('Nebulon-B Frigate'),
        'gametext' => clienttranslate("Choose: Repair 3 daamage from your base, or gain 3 resources"),
        'img' => CardIds::NEBULON_B_FRIGATE,
        'type' => CARD_TYPE_SHIP,
        'faction' => FACTION_NEUTRAL,
        'cost' => 5,
        'stats' => ['power' => 0, 'resource' => 0, 'force' => 0],
        'abilities' => [
            [
                'trigger' => TRIGGER_ACTIVATE_CARD,
                'effects' => [
                    [
                        'type' => EFFECT_CHOICE_OPTION,
                        'options' => [
                            [
                                'label' => clienttranslate('Repair 3 damage from your base'),
                                'type' => EFFECT_REPAIR_DAMAGE_BASE,
                                'conditions' => [
                                    ['type' => CONDITION_HAS_DAMAGE_ON_BASE],
                                ],
                                'value' => 3,
                            ],
                            [
                                'label' => clienttranslate('Gain 3 resources'),
                                'type' => EFFECT_GAIN_RESOURCE,
                                'amount' => 3,
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],

    CardIds::LANDO_CALRISSIAN => [
        'name' => clienttranslate('Lando Calrissian'),
        'img' => CardIds::LANDO_CALRISSIAN,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 6,
        'unique' => true,
        'stats' => ['power' => 3, 'resource' => 3, 'force' => 0],
        'abilities' => [
            [
                'trigger' => TRIGGER_ACTIVATE_CARD,
                'effects' => [
                    [
                        'type' => EFFECT_DRAW_CARD,
                        'value' => 1,
                    ],
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
                                    'selectionMode' => SELECTION_MODE_OPPONENT_CHOICE
                                ],
                                'storeAs' => 'lando_discard',
                            ],
                            [
                                'type' => EFFECT_MOVE_SELECTED_CARDS,
                                'target' => TARGET_OPPONENT,
                                'destination' => ZONE_DISCARD,
                                'cardRef' => 'lando_discard',
                            ]
                        ]
                    ],
                ],
            ]
        ]
    ],

    CardIds::JABBA_SAIL_BARGE => [
        'name' => clienttranslate('Jabba\'s Sail Barge'),
        'img' => CardIds::JABBA_SAIL_BARGE,
        'type' => CARD_TYPE_SHIP,
        'faction' => FACTION_NEUTRAL,
        'cost' => 7,
        'stats' => ['power' => 4, 'resource' => 3, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::JABBA_THE_HUTT => [
        'name' => clienttranslate('Jabba the Hutt'),
        'gametext' => clienttranslate("Exile 1 card from your hand to draw 1 card (2 cards instead if the Force is with you)"),
        'img' => CardIds::JABBA_THE_HUTT,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 8,
        'unique' => true,
        'stats' => ['power' => 2, 'resource' => 2, 'force' => 2],
        'abilities' => [
            [
                'trigger' => TRIGGER_ACTIVATE_CARD,
                'conditions' => [
                    [
                        'type' => CONDITION_HAS_CARDS,
                        'target' => ['zones' => [TARGET_SCOPE_SELF_HAND]]
                    ],
                ],
                'effects' => [
                    [
                        'type' => EFFECT_SELECT_CARDS,
                        'target' => [
                            'zones' => [TARGET_SCOPE_SELF_HAND],
                        ],
                        'storeAs' => 'jabba_exile',
                    ],
                    [
                        'type' => EFFECT_MOVE_SELECTED_CARDS,
                        'target' => TARGET_SELF,
                        'destination' => ZONE_EXILE,
                        'cardRef' => 'jabba_exile',
                    ],
                    [
                        'type' => EFFECT_DRAW_CARD,
                        'conditions' => [
                            ['type' => CONDITION_FORCE_IS_WITH_YOU],
                        ],
                        'value' => 2,
                    ],
                    [
                        'type' => EFFECT_DRAW_CARD,
                        'conditions' => [
                            ['type' => CONDITION_FORCE_IS_NOT_WITH_YOU]
                        ],
                        'value' => 1,
                    ],
                ],
            ]
        ]
    ],

    CardIds::JAWA_SCAVENGER => [
        'name' => clienttranslate('Jawa Scavenger'),
        'img' => CardIds::JAWA_SCAVENGER,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 1,
        'stats' => ['power' => 0, 'resource' => 2, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::RODIAN_GUNSLINGER => [
        'name' => clienttranslate('Rodian Gunslinger'),
        'img' => CardIds::RODIAN_GUNSLINGER,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 2,
        'stats' => ['power' => 2, 'resource' => 0, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::KEL_DOR_MYSTIC => [
        'name' => clienttranslate('Kel Dor Mystic'),
        'img' => CardIds::KEL_DOR_MYSTIC,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 2,
        'stats' => ['power' => 0, 'resource' => 0, 'force' => 2],
        'abilities' => [],
    ],

    CardIds::LOBOT => [
        'name' => clienttranslate('Lobot'),
        'img' => CardIds::LOBOT,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 3,
        'unique' => true,
        'stats' => ['power' => 0, 'resource' => 0, 'force' => 0],
        'abilities' => []
    ],

    CardIds::BOSSK => [
        'name' => clienttranslate('Bossk'),
        'img' => CardIds::BOSSK,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 3,
        'unique' => true,
        'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
        'abilities' => []
    ],

    CardIds::FANG_FIGHTER => [
        'name' => clienttranslate('Fang Fighter'),
        'img' => CardIds::FANG_FIGHTER,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 3,
        'stats' => ['power' => 3, 'resource' => 0, 'force' => 0],
        'abilities' => [
            [
                'trigger' => TRIGGER_WHEN_PURCHASED,
                'effects' => [
                    [
                        'type' => EFFECT_MOVE_CARD,
                        'target' => TARGET_SELF,
                        'destination' => ZONE_HAND
                    ],
                    [
                        'type' => EFFECT_DRAW_CARD,
                        'conditions' => [
                            ['type' => CONDITION_FORCE_IS_WITH_YOU],
                        ],
                        'value' => 1,
                    ]
                ],
            ]
        ],
    ],

    CardIds::TWILEK_SMUGGLER => [
        'name' => clienttranslate('Twi\'lek Smuggler'),
        'img' => CardIds::TWILEK_SMUGGLER,
        'type' => CARD_TYPE_UNIT,
        'faction' => FACTION_NEUTRAL,
        'cost' => 3,
        'stats' => ['power' => 0, 'resource' => 3, 'force' => 0],
        'abilities' => [],
    ],

    CardIds::CROC_CRUISER => [
        'name' => clienttranslate('Croc Cruiser'),
        'img' => CardIds::CROC_CRUISER,
        'type' => CARD_TYPE_SHIP,
        'faction' => FACTION_NEUTRAL,
        'cost' => 3,
        'stats' => ['power' => 0, 'resource' => 1, 'force' => 0],
        'abilities' => [],
    ],
];

$neutral_deck_composition = [
    CardIds::Z95_HEADHUNTER => 2,
    CardIds::JAWA_SCAVENGER => 2,
    CardIds::RODIAN_GUNSLINGER => 2,
    CardIds::KEL_DOR_MYSTIC => 2,
    CardIds::LOBOT => 1,
    CardIds::BOSSK => 1,
    CardIds::FANG_FIGHTER => 2,
    CardIds::TWILEK_SMUGGLER => 2,
    CardIds::CROC_CRUISER => 2,
    CardIds::DENGAR => 1,
    CardIds::QUARREN_MERCENARY => 2,
    CardIds::HWK_290 => 2,
    CardIds::BLOCKADE_RUNNER => 3,
    CardIds::IG_88 => 1,
    CardIds::NEBULON_B_FRIGATE => 2,
    CardIds::LANDO_CALRISSIAN => 1,
    CardIds::JABBA_SAIL_BARGE => 1,
    CardIds::JABBA_THE_HUTT => 1,
];
