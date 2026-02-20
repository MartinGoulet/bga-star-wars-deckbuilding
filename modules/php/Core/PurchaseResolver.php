<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use CardInstance;

/**
 * Handles the logic for resolving card purchases in the game.
 */
final class PurchaseResolver {
    /**
     * @var GameContext The current game context, injected via constructor.
     */
    public function __construct(private GameContext $ctx) {
        // No initialization needed beyond context assignment
    }

    /**
     * Resolves the purchase of a card by the active player.
     * Deducts resources, presents purchase options, and triggers effects.
     *
     * @param int $cardId The ID of the card being purchased.
     * @return string The next state to transition to after resolving the purchase.
     */
    public function resolvePurchase(int $cardId): string {
        // Increment the number of purchases this round
        $this->ctx->game->nbrPurchasesThisRound->inc(1);

        $activePlayerId = $this->ctx->currentPlayer()->playerId;
        $card = $this->ctx->cardRepository->getCard($cardId);

        // Deduct the card's cost from the active player's resources
        $this->ctx->game->playerResources->inc($activePlayerId, -$card->cost);

        // Notify players
        $this->ctx->game->notify->all(
            'message',
            clienttranslate('${player_name} purchases ${card_name} from the Galaxy Row'),
            [
                'player_id' => $activePlayerId,
                'card' => $card,
            ]
        );

        // Add possible purchase effects as choices for the player
        $engine = $this->ctx->getGameEngine();
        $engine->addChoiceEffect(
            $card,
            TARGET_SELF,
            $this->getPurchaseOptions($activePlayerId, $card, $this->ctx)
        );

        $this->consumeOverrides();
        
        $result = $engine->run();

        return $result;
    }

    private function consumeOverrides(): void {
        $overrides = $this->ctx->globals->get(GVAR_PURCHASE_OPTION_OVERRIDES, []);

        $overrides = array_filter(
            $overrides,
            fn($o) => $o['expires'] !== 'after_next_purchase'
        );

        $this->ctx->globals->set(GVAR_PURCHASE_OPTION_OVERRIDES, $overrides);
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

        // Default option: place the purchased card in the discard pile
        $options[] = [
            'label' => clienttranslate('Place in discard pile'),
            'type' => EFFECT_MOVE_CARD,
            'target' => TARGET_SELF,
            'destination' => ZONE_DISCARD,
        ];

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

        $options = array_merge($options, $this->getOverrideOptions());

        return $options;
    }

    /**
     * Returns the option array for using the active base's ability, or null if not available.
     */
    private function getBaseAbilityOption(int $activePlayerId, GameContext $ctx): ?array {
        $activeBase = $this->ctx->cardRepository->getActiveBase($activePlayerId);
        if ($activeBase !== null) {
            $baseEffect = $this->getPurchaseEffect($activeBase);
            if ($baseEffect !== null && $baseEffect->canResolve($ctx)) {
                $option = [
                    'label' => clienttranslate('Use ability of ${card_name}'),
                    'labelArgs' => ['card_name' => $activeBase->name],
                    'cardId' => $activeBase->id,
                ];
                $options = array_merge($option, $baseEffect->definition);
                return $options;
            }
        } else {
            die('no active base');
        }
        return null;
    }

    /**
     * Returns the option array for using the purchased card's ability, or null if not available.
     */
    private function getCardAbilityOption(CardInstance $card, GameContext $ctx): ?array {
        $cardEffect = $this->getPurchaseEffect($card);
        
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
    private function getPurchaseEffect(CardInstance $card): EffectInstance|null {
        $effects = $card->getEffect(TRIGGER_WHEN_PURCHASED, $this->ctx);
        if ($effects === null || empty($effects)) {
            return null;
        }
        $effect = current($effects);
        $effect['sourceCardId'] = $card->id;
        return EffectFactory::createEffectInstance($effect);
    }

    private function getOverrideOptions(): array {
        $options = [];

        $overrides = $this->ctx->globals->get(GVAR_PURCHASE_OPTION_OVERRIDES, []);

        foreach ($overrides as $override) {
            $override['option']['labelArgs'] = $override['option']['labelArgs'] ?? [];
            $options[] = $override['option'];
        }

        return $options;
    }
}
