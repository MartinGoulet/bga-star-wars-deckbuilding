<?php


/**
 * States
 */

const ST_GAME_SETUP = 1;
const ST_GAME_END = 99;

// const ST_PLAYER_TURN = 10;
const ST_PLAYER_TURN_ACTION_SELECTION = 10;
const ST_PLAYER_TURN_ASK_CHOICE = 11;
const ST_PLAYER_TURN_ATTACK_DECLARATION = 12;
const ST_PLAYER_TURN_ATTACK_COMMIT = 13;
const ST_PLAYER_TURN_ATTACK_RESOLVE = 14;
const ST_PLAYER_TURN_END_TURN = 15;
const ST_PLAYER_TURN_START_TURN_BASE = 16;
const ST_PLAYER_TURN_START_TURN_RESOURCES = 17;
const ST_PLAYER_TURN_ATTACK_RESOLVE_DAMAGE_SHIP_BASE = 18;
const ST_PLAYER_TURN_START_TURN = 19;

const ST_PURCHASE_BEGIN = 40;
const ST_PURCHASE_DESTINATION = 41;
const ST_PURCHASE_END = 42;

const ST_EFFECT_CARD_SELECTION = 71;
const ST_EFFECT_CHOICE = 72;

/**
 * Global Variables
 */
const GVAR_DAMAGE_ON_CARDS = 'damageOnCards';
const GVAR_ATTACK_TARGET_CARD_ID = 'attackTargetCardId';
const GVAR_ATTACKERS_CARD_IDS = 'attackersCardIds';
const GVAR_ALREADY_ATTACKING_CARDS_IDS = 'alreadyAttackingCardsIds';
const GVAR_ABILITY_USED_CARD_IDS = 'abilityUsedCardIds';
const GVAR_EFFECTS_TO_RESOLVE = 'effectsToResolve';
const GVAR_ATTACK_MODIFIER_PER_CARDS = 'attackModifierPerCards';
const GVAR_REMAINING_DAMAGE_TO_ASSIGN = 'remainingDamageToAssign';
const GVAR_GALAXY_DECK_REVEALED_CARD = 'galaxy_deck_revealed_card';
const GVAR_DELAYED_EFFECTS = 'delayed_effects';
const GVAR_PURCHASE_OPTION_OVERRIDES = 'purchase_option_overrides';
const GVAR_PREVENT_DAMAGE_PER_TURN_EFFECTS = 'prevent_damage_per_turn_effects';
const GVAR_PURCHASE_CARD_ID = 'purchase_card_id';
const GVAR_PURCHASE_DESTINATIONS = 'purchase_destinations';

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
const CARD_TYPE_BASE = 'BASE';

/**
 * Abilities
 */
const ABILITY_DRAW_CARD = 'draw_card';
const ABILITY_CHOICE = 'choice';
const ABILITY_STATIC_ATTACK_MODIFIER = 'static_attack_modifier';
const ABILITY_AURA_ATTACK_MODIFIER = 'aura_attack_modifier';

/**
 * Conditions
 */
const CONDITION_ANOTHER_UNIQUE_UNIT_IN_PLAY = 'another_unique_unit_in_play';
const CONDITION_CAPITAL_STARSHIP_IN_PLAY = 'capital_starship_in_play';
const CONDITION_BASE_IS_DEATH_STAR = 'base_is_death_star';
const CONDITION_FORCE_IS_WITH_YOU = 'force_is_with_you';
const CONDITION_FORCE_IS_NOT_WITH_YOU = 'force_is_not_with_you';
const CONDITION_HAS_DAMAGE_ON_BASE = 'has_damage_on_base';
const CONDITION_CARD_IN_PLAY = 'card_in_play';
const CONDITION_FIRST_PURCHASE_THIS_TURN = 'first_purchase_this_turn';
const CONDITION_CARD_FACTION_IS = 'card_faction_is';
const CONDITION_HAS_RESOURCES = 'has_resources';
const CONDITION_HAS_CARDS = 'has_cards';
const CONDITION_HAS_CARDS_REFERENCE = 'has_cards_reference';
const CONDITION_THIS_CARD_WAS_ATTACKER = 'this_card_was_attacker';
const CONDITION_DEFEATED_IN_ZONE = 'defeated_in_zone';
 
/**
 * Effects
 */
const EFFECT_CHOICE = 'choice';

const EFFECT_MODIFY_ATTACK = 'modify_attack';
const EFFECT_CONDITIONAL = 'conditional_effect';
const EFFECT_DRAW_CARD = 'draw';
const EFFECT_GAIN_RESOURCE = 'gain_resource';
const EFFECT_GAIN_ATTACK = 'gain_attack';
const EFFECT_GAIN_FORCE = 'gain_force';
const EFFECT_REPAIR_DAMAGE_BASE = 'repair_damage_base';
const EFFECT_PURCHASE_CARD_FREE = 'purchase_card_free';

const EFFECT_CHOICE_OPTION = 'choice_option';
const EFFECT_SELECT_CARDS = 'select_cards';
const EFFECT_MOVE_SELECTED_CARDS = 'move_selected_cards';
const EFFECT_REVEAL_TOP_CARD = 'reveal_top_card';
const EFFECT_PAY_RESOURCE = 'pay_resource';
const EFFECT_DESTROY_SELECTED_CARD = 'destroy_selected_card';
const EFFECT_REVEAL_CARDS = 'reveal_cards';
const EFFECT_HIDE_CARDS = 'hide_cards';
const EFFECT_SELECT_CURRENT_CARD = 'select_current_card';
const EFFECT_DEAL_BASE_DAMAGE = 'deal_base_damage';
const EFFECT_REMOVE_CARD_REFERENCE = 'remove_card_reference';
const EFFECT_REGISTER_DELAYED = 'register_delayed_effect';

const EFFECT_EXILE_CARD = 'exile_card';
const EFFECT_MOVE_CARD = 'move_card';
const EFFECT_REGISTER_PURCHASE_OPTION = 'register_purchase_option';
const EFFECT_PREVENT_DAMAGE_PER_TURN = 'prevent_damage_per_turn';
const EFFECT_ASSIGN_PURCHASE_DESTINATION = 'assign_purchase_destination';

/**
 * Triggers
 */
const TRIGGER_ON_PURCHASE_BEGIN = 'on_purchase_begin';
const TRIGGER_ON_PURCHASE_DESTINATION = 'on_purchase_destination';
const TRIGGER_ON_PURCHASE_END = 'on_purchase_end';

const TRIGGER_WHEN_PURCHASED = 'when_purchased';
const TRIGGER_DEFEAT_TARGET_GALAXY_ROW = 'defeat_target_galaxy_row';
const TRIGGER_END_OF_TURN = 'end_of_turn';

const TRIGGER_ON_PLAY = 'ON_PLAY';
const TRIGGER_REWARD = 'REWARD';
const TRIGGER_ACTIVATE_CARD = 'ON_ACTIVATE';
const TRIGGER_WHILE_IN_PLAY = 'WHILE_IN_PLAY';
const TRIGGER_ON_CARD_DEFEATED = 'ON_CARD_DEFEATED';
const TRIGGER_ON_REVEAL_BASE = 'ON_REVEAL_BASE';

/**
 * Targets
 */
const TARGET_OPPONENT = 'opponent';
const TARGET_SELF = 'self';

/**
 * Filters
 */
const FILTER_HAS_TRAIT = 'has_trait';
const FILTER_CARD_TYPES = 'card_types';
const FILTER_FACTIONS = 'factions';
const FILTER_ABILITIES = 'abilities';
const FILTER_UNIQUE = 'unique';

/**
 * Traits
 */
const TRAIT_TROOPER = 'Trooper';
const TRAIT_FIGHTER = 'Fighter';
const TRAIT_VEHICLE = 'Vehicle';
const TRAIT_TRANSPORT = 'Transport';
const TRAIT_OFFICER = 'Officer';
const TRAIT_BOUNTY_HUNTER = 'Bounty Hunter';
const TRAIT_SITH = 'Sith';
const TRAIT_DROID = 'Droid';

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
const ZONE_PLAYER_SHIP_AREA = 'ship_area';
const ZONE_PLAYER_DISCARD = 'player_discard';
const ZONE_PLAYER_DECK = 'player_deck';

const ZONE_GALAXY_ROW = 'galaxy_row';
const ZONE_GALAXY_DECK = 'deck';
const ZONE_GALAXY_DISCARD = 'galaxy_discard';
const ZONE_EXILE = 'exile';
const ZONE_OUTER_RIM_DECK = 'outer_rim_deck';
const ZONE_TOP_DECK = 'top_deck';
const ZONE_CONDITIONAL = 'conditional';

/**
 * Targeting
 */

const TARGET_SCOPE_SELF_PLAY_AREA = 'self_play_area';
const TARGET_SCOPE_SELF_SHIP_AREA = 'self_ship_area';
const TARGET_SCOPE_SELF_DISCARD = 'self_discard';
const TARGET_SCOPE_SELF_HAND = 'self_hand';
const TARGET_SCOPE_SELF_BASE = 'self_base';
const TARGET_SCOPE_OPPONENT_PLAY_AREA = 'opponent_play_area';
const TARGET_SCOPE_OPPONENT_SHIP_AREA = 'opponent_ship_area';
const TARGET_SCOPE_OPPONENT_DISCARD = 'opponent_discard';
const TARGET_SCOPE_OPPONENT_HAND = 'opponent_hand';
const TARGET_SCOPE_OPPONENT_BASE = 'opponent_base';
const TARGET_SCOPE_GALAXY_ROW = 'galaxy_row';
const TARGET_SCOPE_GALAXY_DECK = 'galaxy_deck';
const TARGET_SCOPE_GALAXY_DISCARD = 'galaxy_discard';

const SELECTION_MODE_PLAYER_CHOICE = 'player_choice';
const SELECTION_MODE_OPPONENT_CHOICE = 'opponent_choice';
const SELECTION_MODE_RANDOM = 'random';


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
