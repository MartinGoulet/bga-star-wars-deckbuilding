<?php


/**
 * States
 */

const ST_GAME_SETUP = 1;
const ST_GAME_END = 99;

// const ST_PLAYER_TURN = 10;
const ST_PLAYER_TURN_ACTION_SELECTION = 10;
const ST_PLAYER_TURN_ASK_CHOICE = 11;

/**
 * Factions
 */
const FACTION_REBEL = 'Rebel';
const FACTION_EMPIRE = 'Empire';
const FACTION_NEUTRAL = 'Neutral';

/**
 * Card types
 */
const CARD_TYPE_UNIT = 'Unit';
const CARD_TYPE_SHIP = 'Ship';

/**
 * Abilities
 */
const ABILITY_GAIN_RESOURCE = 'gain_resource';
const ABILITY_GAIN_POWER = 'gain_power';
const ABILITY_GAIN_FORCE = 'gain_force';
const ABILITY_DRAW_CARD = 'draw_card';
const ABILITY_CONDITIONAL = 'conditional';
const ABILITY_CHOICE = 'choice';
const ABILITY_DISCARD_CARD_GALAXY_ROW = 'discard_card_galaxy_row';
const ABILITY_REVEAL_GALAXY_ROW_CARD = 'reveal_galaxy_row_card';
const ABILITY_LOOK_AT_TOP_CARD = 'look_at_top_card';
const ABILITY_SWAP_TOP_DECK_WITH_ROW = 'swap_top_deck_with_row';

/**
 * Conditions
 */
const CONDITION_ANOTHER_UNIQUE_UNIT_IN_PLAY = 'another_unique_unit_in_play';
const CONDITION_CAPITAL_STARSHIP_IN_PLAY = 'capital_starship_in_play';
const CONDITION_BASE_IS_DEATH_STAR = 'base_is_death_star';
const CONDITION_FORCE_IS_WITH_YOU = 'force_is_with_you';
const CONDITION_IS_ENEMY_CARD = 'is_enemy_card';
const CONDITION_HAS_TRAIT = 'has_trait';
const CONDITION_HAS_UNIT_IN_PLAY_WITH_TRAIT = 'has_unit_in_play_with_trait';


const CONDITION_OPERATOR_OR = 'operator_or';
const CONDITION_OPERATOR_AND = 'operator_and';


/**
 * Costs
 */
const COST_DISCARD_FROM_HAND = 'discard_from_hand';

/**
 * Effects
 */
const EFFECT_CHOICE = 'choice';

const EFFECT_MODIFY_POWER = 'modify_power';
const EFFECT_DISCARD_CARD = 'discard_card';
const EFFECT_CONDITIONAL = 'conditional_effect';
const EFFECT_DRAW = 'draw';
const EFFECT_GAIN_RESOURCE = 'gain_resource';
const EFFECT_GAIN_POWER = 'gain_power';
const EFFECT_GAIN_FORCE = 'gain_force';

/**
 * Choice Options
 */
const CHOICE_OPTION_GAIN_RESOURCE = 'gain_resource';
const CHOICE_OPTION_GAIN_POWER = 'gain_power';
const CHOICE_OPTION_GAIN_FORCE = 'gain_force';
const CHOICE_OPTION_REPAIR_DAMAGE_BASE = 'repair_damage_base';

/**
 * Costs
 */
const COST_EXILE_SELF = 'exile_self';

/**
 * Effects
 */
const EFFECT_EXILE_CARD = 'exile_card';
const EFFECT_MOVE_CARD = 'move_card';

/**
 * Triggers
 */
const TRIGGER_WHEN_PURCHASED = 'when_purchased';
const TRIGGER_ON_PLAY = 'on_play';
const TRIGGER_ACTIVATE_CARD = 'activate_card';
const TRIGGER_WHILE_IN_PLAY = 'while_in_play';
const TRIGGER_DEFEAT_TARGET_GALAXY_ROW = 'defeat_target_galaxy_row';
const TRIGGER_END_OF_TURN = 'end_of_turn';
const TRIGGER_CONDITIONAL_EFFECT = 'conditional_effect';

/**
 * Targets
 */
const TARGET_YOUR_UNITS = 'your_units';
const TARGET_YOUR_CARDS = 'your_cards';
const TARGET_YOUR_SHIPS = 'your_ships';
const TARGET_OPPONENT = 'opponent';
const TARGET_SELF = 'self';
const TARGET_ANY_CARD = 'any_card';

/**
 * Filters
 */
const FILTER_HAS_TRAIT = 'has_trait';
const FILTER_FACTION = 'faction';

/**
 * Traits
 */
const TRAIT_TROOPER = 'Trooper';
const TRAIT_FIGHTER = 'Fighter';
const TRAIT_VEHICLE = 'Vehicle';
const TRAIT_TRANSPORT = 'Transport';
const TRAIT_OFFICER = 'Officer';
const TRAIT_BOUNTER_HUNTER = 'Bounty Hunter';
const TRAIT_SITH = 'Sith';

const TRAIT_EMPIRE = 'Empire';
const TRAIT_REBEL = 'Rebel';

/**
 * Selection Modes
 */
const SELECTION_PLAYER_CHOICE = 'player_choice';

/**
 * Zones
 */
const ZONE_HAND = 'hand';
const ZONE_DECK = 'deck';
const ZONE_DISCARD = 'discard';
const ZONE_PLAYER_PLAY_AREA = 'inplay_';
const ZONE_GALAXY_ROW = 'galaxy_row';
const ZONE_GALAXY_DECK = 'deck';
const ZONE_GALAXY_DISCARD = 'galaxy_discard';

final class CardIds {

    // Imperial Cards
    public const TIE_FIGHTER = 1;
    public const AT_ST = 2;
    public const LANDING_CRAFT = 3;
    public const DIRECTOR_KRENNIC = 4;
    public const BOBA_FETT = 5;
    public const IMPERIAL_CARRIER = 6;
    public const AT_AT = 7;
    public const GRAND_MOFF_TARKIN = 8;
    public const STAR_DESTROYER = 9;
    public const DARTH_VADER = 10;
    public const ADMIRAL_PIETT = 11;
    public const TIE_BOMBER = 12;
    public const SCOUT_TROOPER = 13;
    public const DEATH_TROOPER = 14;
    public const TIE_INTERCEPTOR = 15;
    public const GOZANTI_CRUISER = 16;
    public const MOFF_JERJERROD = 17;
    public const GENERAL_VEERS = 18;

    // Neutral Cards
    public const Z95_HEADHUNTER = 19;
    public const DENGAR = 20;
    public const QUARREN_MERCENARY = 21;
    public const HWK_290 = 22;
    public const BLOCKADE_RUNNER = 23;
    public const IG_88 = 24;
    public const NEBULON_B_FRIGATE = 25;
    public const LANDO_CALRISSIAN = 26;
    public const JABBA_SAIL_BARGE = 27;
    public const JABBA_THE_HUTT = 28;
    public const JAWA_SCAVENGER = 29;
    public const RODIAN_GUNSLINGER = 30;
    public const KEL_DOR_MYSTIC = 31;
    public const LOBOT = 32;
    public const BOSSK = 33;
    public const FANG_FIGHTER = 34;
    public const TWILEK_SMUGGLER = 35;
    public const CROC_CRUISER = 36;

    // Rebel Cards
    public const Y_WING = 37;
    public const JYN_ERSO = 38;
    public const U_WING = 39;
    public const HAMMERHEAD_CORVETTE = 40;
    public const HAN_SOLO = 41;
    public const CASSIAN_ANDOR = 42;
    public const B_WING = 43;
    public const PRINCESS_LEIA = 44;
    public const MON_CALAMARI_CRUISER = 45;
    public const MILLENNIUM_FALCON = 46;
    public const LUKE_SKYWALKER = 47;
    public const BAZE_MALBUS = 48;
    public const SNOWSPEEDER = 49;
    public const DUROS_SPY = 50;
    public const REBEL_TRANSPORT = 51;
    public const CHIRRUT_IMWE = 52;
    public const REBEL_COMMANDO = 53;
    public const X_WING = 54;
    public const CHEWBACCA = 55;

    // Starter Empire
    public const IMPERIAL_SHUTTLE = 56;
    public const STORMTROOPER = 57;
    public const INQUISITOR = 58;
    // Starter Rebels
    public const ALLIANCE_SHUTTLE = 59;
    public const REBEL_TROOPER = 60;
    public const TEMPLE_GUARDIAN = 61;
    // Generic
    public const OUTER_RIM_PILOT = 62;


    // Empire - Bases
    public const LOTHAL = 70;
    public const CORRELIA = 71;
    public const KESSEL = 72;
    public const KAFRENE = 73;
    public const MUSTAFAR = 74;
    public const ORD_MANTELL = 75;
    public const CORUSCANT = 76;
    public const DEATH_STAR = 77;
    public const ENDOR = 78;
    public const RODIA = 79;
    // Rebel - Bases
    public const DANTOOINE = 80;
    public const MON_CALA = 81;
    public const DAGOBAH = 82;
    public const BESPIN = 83;
    public const ALDERAAN = 84;
    public const HOTH = 85;
    public const JEDHA = 86;
    public const TATOOINE = 87;
    public const SULLUST = 88;
    public const YAVIN_4 = 89;
}
