<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ChoiceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ConditionalEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\DiscardCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\DrawCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ExileCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainAttackEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainForceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainResourceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\MoveCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\MoveSelectedCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\PayResourceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\PurchaseCardFreeEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RepairDamageBaseEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RevealTopCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\SelectCardEffect;
use Bga\Games\StarWarsDeckbuilding\Game;
use BgaUserException;
use BgaVisibleSystemException;

final class EffectFactory {
    public static function createEffectInstance(array $data): EffectInstance {
        $conditions = $data['conditions'] ?? [];
        $sourceCardId = $data['sourceCardId'] ?? null;
        if($sourceCardId == null) {
            throw new BgaVisibleSystemException("Effect definition must include sourceCardId");
            // $sourceCard = null;
        } else {

            $sourceCard = Game::get()->cardRepository->getCardById($sourceCardId);
        }

        switch ($data['type']) {
            case EFFECT_CHOICE:
                $value = new ChoiceEffect(
                    $data['target'] ?? TARGET_SELF,
                    $data['options']
                );
                break;
            case EFFECT_MOVE_CARD:
                $value = new MoveCardEffect($data['target'], $data['destination']);
                break;
            
            case EFFECT_DRAW_CARD:
                $value = new DrawCardEffect(
                    $data['value'],
                    $data['overrideValue'] ?? [],
                );
                break;
            case EFFECT_DISCARD_CARD:
                $value = new DiscardCardEffect(
                    $data['target'],
                    $data['count'],
                );
                break;
            case EFFECT_EXILE_CARD:
                $value = new ExileCardEffect(
                    $data['target'],
                    $data['count'],
                    $data['zones'] ?? [ZONE_HAND, ZONE_DISCARD, ZONE_DECK],
                );
                break;
            case EFFECT_GAIN_RESOURCE:
                $value = new GainResourceEffect($data['count']);
                break;
            case EFFECT_GAIN_ATTACK:
                $value = new GainAttackEffect($data['count']);
                break;
            case EFFECT_GAIN_FORCE:
                $value = new GainForceEffect($data['count']);
                break;
            case EFFECT_REPAIR_DAMAGE_BASE:
                $value = new RepairDamageBaseEffect($data['value']);
                break;
            case EFFECT_PURCHASE_CARD_FREE:
                $value = new PurchaseCardFreeEffect(
                    $data['factions'] ?? [],
                    $data['destination'] ?? ZONE_DISCARD,
                    $data['destinationMapping'] ?? []
                );
                break;
            case EFFECT_SELECT_CARDS:
                $value = new SelectCardEffect(
                    $data['target'] ?? TARGET_SELF,
                    $data['from'],
                    $data['count'],
                    $data['filters'] ?? [],
                    $data['storeAs'],
                    $data['random'] ?? false,
                );
                break;
            case EFFECT_MOVE_SELECTED_CARDS:
                $value = new MoveSelectedCardEffect(
                    $data['target'] ?? TARGET_SELF,
                    $data['to'],
                    $data['cardRef'],
                );
                break;
            case EFFECT_REVEAL_TOP_CARD:
                $value = new RevealTopCardEffect(
                    $data['from'],
                    $data['storeAs'] ?? '',
                );
                break;
            case EFFECT_CONDITIONAL:
                $value = new ConditionalEffect(
                    $data['effects']
                );
                break;
            case EFFECT_PAY_RESOURCE:
                $value = new PayResourceEffect(
                    $data['amount']
                );
                break;
            default:    
                throw new \InvalidArgumentException("Unknown effect type: " . $data['type']);
        }

        $value->conditions = $conditions;
        $value->sourceCard = $sourceCard;
        $value->definition = $data;
        return $value;
    }

    public static function createEffects(array $effectsData): array {
        $effects = [];
        foreach ($effectsData as $effectData) {
            $effects[] = self::createEffectInstance($effectData);
        }
        return $effects;
    }
}
