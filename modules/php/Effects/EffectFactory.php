<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects;

use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ChoiceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ChoiceOptionEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ConditionalEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\DestroyCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\DrawCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\ExileCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainAttackEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainForceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\GainResourceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\HideCardsEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\MoveCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\MoveSelectedCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\PayResourceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\PurchaseCardFreeEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RegisterDelayedEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RegisterPurchaseOptionEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RemoveCardReferenceEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RepairDamageBaseEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RevealCardsEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\RevealTopCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\SelectCardEffect;
use Bga\Games\StarWarsDeckbuilding\Effects\Concrete\SelectCurrentCardEffect;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQueryFactory;
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

        if (!isset($data['type'])) {
            var_dump($data);
            die('Effect definition must include type');
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
                    $data['amount'],
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
                $value = new GainResourceEffect($data['amount']);
                break;
            case EFFECT_GAIN_ATTACK:
                $value = new GainAttackEffect($data['amount']);
                break;
            case EFFECT_GAIN_FORCE:
                $value = new GainForceEffect($data['amount']);
                break;
            case EFFECT_REPAIR_DAMAGE_BASE:
                $value = new RepairDamageBaseEffect($data['amount']);
                break;
            case EFFECT_PURCHASE_CARD_FREE:
                $value = new PurchaseCardFreeEffect($data['cardRef']);
                break;
            case EFFECT_SELECT_CARDS:
                $target = TargetQueryFactory::create($data['target']);
                $value = new SelectCardEffect(
                    $target,
                    $data['storeAs'],
                );
                break;
            case EFFECT_MOVE_SELECTED_CARDS:
                $value = new MoveSelectedCardEffect(
                    $data['target'] ?? TARGET_SELF,
                    $data['destination'],
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
                $value = new ConditionalEffect($data['effects']);
                break;
            case EFFECT_PAY_RESOURCE:
                $value = new PayResourceEffect($data['amount']);
                break;
            case EFFECT_DESTROY_SELECTED_CARD:
                $value = new DestroyCardEffect($data['cardRef']);
                break;
            case EFFECT_CHOICE_OPTION:
                $value = new ChoiceOptionEffect(
                    $data['target'] ?? TARGET_SELF,
                    $data['options']
                );
                break;
            case EFFECT_REVEAL_CARDS:
                $value = new RevealCardsEffect(
                    $data['cardRef'],
                );
                break;
            case EFFECT_HIDE_CARDS:
                $value = new HideCardsEffect($data['cardRef']);
                break;
            case EFFECT_SELECT_CURRENT_CARD:
                $value = new SelectCurrentCardEffect(
                    $data['storeAs'],
                );
                break;
            case EFFECT_REMOVE_CARD_REFERENCE:
                $value = new RemoveCardReferenceEffect(
                    $data['cardRef'],
                );
                break;
            case EFFECT_REGISTER_DELAYED:
                $value = new RegisterDelayedEffect(
                    $data['trigger'],
                    $data['effects'],
                );
                break;
            case EFFECT_REGISTER_PURCHASE_OPTION:
                $value = new RegisterPurchaseOptionEffect();
                break;
            default:    
                var_dump([
                    'error' => 'Unknown effect type',
                    'type' => $data['type'],
                ]);
                die("Unknown effect type: " . $data['type']);
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
