<?php

declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQueryFactory;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetResolver;
use CardInstance;

class Purchase_Destination extends GameState {
    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: ST_PURCHASE_DESTINATION,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array {
        return ['_no_notify' => true];
    }

    function onEnteringState(int $activePlayerId) {

        $ctx = new GameContext($this->game);
        $engine = $ctx->getGameEngine();
        $engine->setNextState(Purchase_End::class);

        $target = TargetQueryFactory::create([
            'zones' => [TARGET_SCOPE_SELF_BASE, TARGET_SCOPE_SELF_PLAY_AREA, TARGET_SCOPE_SELF_SHIP_AREA],
        ]);

        $cards = (new TargetResolver($ctx))->resolve($target);

        $destinations = [];

        foreach ($cards as $card) {
            $effects = $card->getEffect(TRIGGER_ON_PURCHASE_DESTINATION, $ctx);
            if (!empty($effects)) {
                $effect = array_shift($effects);
                $destinations[] = [
                    'cardId' => $card->id,
                    'destination' => $effect['destination'],
                ];
            }
        }

        $this->globals->set(GVAR_PURCHASE_DESTINATIONS, $destinations);

        $card = $this->game->cardRepository->getCard($this->globals->get(GVAR_PURCHASE_CARD_ID));

        $options = $this->getPurchaseOptions($activePlayerId, $card, $ctx);
        if (!empty($options)) {

            $engine->addChoiceEffect(
                $card,
                TARGET_SELF,
                $this->getPurchaseOptions($activePlayerId, $card, $ctx)
            );

            return $engine->run();
        }

        return Purchase_End::class;
    }

    /**
     * Returns the list of options available to the player when purchasing a card.
     * This may include placing the card in the discard pile or triggering special abilities.
     *
     * @param int $activePlayerId The ID of the active player.
     * @param CardInstance $card The card being purchased.
     * @param GameContext $ctx The current game context.
     * @return array The list of purchase options.
     */
    private function getPurchaseOptions(int $activePlayerId, CardInstance $card, GameContext $ctx): array {
        $options = [];

        // Add base ability option if available
        $baseOption = $this->getBaseAbilityOption($activePlayerId, $ctx);
        if ($baseOption !== null) {
            $options[] = $baseOption;
        }

        // Add card ability option if available
        $cardOption = $this->getCardAbilityOption($card, $ctx);
        if ($cardOption !== null) {
            $options[] = $cardOption;
        }

        $options = array_merge($options, $this->getOverrideOptions($ctx));

        // // Default option: place the purchased card in the discard pile
        // $options[] = [
        //     'label' => clienttranslate('Place in discard pile'),
        //     'type' => EFFECT_MOVE_CARD,
        //     'target' => TARGET_SELF,
        //     'destination' => ZONE_DISCARD,
        // ];

        return $options;
    }

    /**
     * Returns the option array for using the active base's ability, or null if not available.
     */
    private function getBaseAbilityOption(int $activePlayerId, GameContext $ctx): ?array {
        $activeBase = $ctx->cardRepository->getActiveBase($activePlayerId);
        if ($activeBase !== null) {
            $baseEffect = $this->getPurchaseEffect($activeBase, $ctx);
            if ($baseEffect !== null && $baseEffect->canResolve($ctx)) {
                $option = [
                    'label' => clienttranslate('Use ability of ${card_name}'),
                    'labelArgs' => ['card_name' => $activeBase->name],
                    'cardId' => $activeBase->id,
                ];
                $mergedOption = array_merge($option, $baseEffect->definition);
                return $mergedOption;
            }
        }
        return null;
    }

    /**
     * Returns the option array for using the purchased card's ability, or null if not available.
     */
    private function getCardAbilityOption(CardInstance $card, GameContext $ctx): ?array {
        $cardEffect = $this->getPurchaseEffect($card, $ctx);

        if ($cardEffect !== null && $cardEffect->canResolve($ctx)) {
            $option = [
                'label' => clienttranslate('Use ability of ${card_name}'),
                'labelArgs' => ['card_name' => $card->name],
                'cardId' => $card->id,
            ];
            $option = array_merge($option, $cardEffect->definition);
            return $option;
        }
        return null;
    }

    /**
     * Retrieves the effect instance for a card's purchase trigger, if any.
     *
     * @param CardInstance $card The card to check for a purchase effect.
     * @return EffectInstance|null The effect instance, or null if none exists.
     */
    private function getPurchaseEffect(CardInstance $card, GameContext $ctx): EffectInstance|null {
        $effects = $card->getEffect(TRIGGER_WHEN_PURCHASED, $ctx);
        if ($effects === null || empty($effects)) {
            return null;
        }
        $effect = current($effects);
        $effect['sourceCardId'] = $card->id;
        return EffectFactory::createEffectInstance($effect);
    }

    private function getOverrideOptions(GameContext $ctx): array {
        $options = [];

        $overrides = $ctx->globals->get(GVAR_PURCHASE_OPTION_OVERRIDES, []);

        foreach ($overrides as $override) {
            $override['option']['labelArgs'] = $override['option']['labelArgs'] ?? [];
            $options[] = $override['option'];
        }

        return $options;
    }
}
