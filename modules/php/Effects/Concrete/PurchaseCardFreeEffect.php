<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;

final class PurchaseCardFreeEffect extends EffectInstance implements NeedsPlayerInput {
    public function __construct(
        private array $factions,
        private string $destination,
        private array $destinationMapping,
    ) {
    }

    public function resolve(GameContext $ctx): void {
    }

    public function getNextState(): string {
        return Effect_CardSelection::class;
    }

    public function onPlayerChoice(GameContext $ctx, array $data): string {
        // Player pass
        if (!isset($data['cardIds'])) {
            return '';
        }

        $card = $ctx->cardRepository->getCard(current($data['cardIds']));

        $ctx->game->notify->all(
            'message',
            clienttranslate('${player_name} purchases ${card_name} for free'),
            [
                'player_id' => $ctx->currentPlayer()->playerId,
                'card' => $card,
            ]
        );

        $destination = $this->destination === ZONE_CONDITIONAL ? $this->getConditionalDestination($ctx) : $this->destination;
        $ctx->getGameEngine()->addMoveCardEffect($card, TARGET_SELF, $destination);

        return '';
    }

    public function getArgs(GameContext $ctx): array {

        $player_id = $ctx->currentPlayer()->playerId;

        return [
            'nbr' => 1,
            'optional' => false,
            'selectableCards' => array_values($this->getSelectableCards($ctx)),
            'card' => $this->sourceCard,
            'target' => TARGET_SELF,
            'player_name' => $ctx->game->getPlayerNameById($player_id),
            'player_id' => $player_id,
            'description' => clienttranslate('${player_name} must select ${nbr} card(s) to purchase for free'),
            'descriptionMyTurn' => clienttranslate('${you} must select ${nbr} card(s) to purchase for free'),
        ];
    }

    /**
     * @return CardInstance[] Returns an array of selectable CardInstances based on the effect's filters and the current game context.
     */
    private function getSelectableCards(GameContext $ctx): array {
        $galaxyRowCards = $ctx->cardRepository->getGalaxyRow();
        $galaxyRowCards = array_filter($galaxyRowCards, fn($card) => in_array($card->faction, $this->factions));
        return $galaxyRowCards;
    }

    private function getConditionalDestination(GameContext $ctx): string {
        
        foreach ($this->destinationMapping as $zone => $conditionsToMeet) {
            $conditionsToMeet = ConditionFactory::createConditions($conditionsToMeet);
            $canResolve = true;
            foreach ($conditionsToMeet as $condition) {
                if (!$condition->isSatisfied($ctx)) {
                    $canResolve = false;
                    break;  
                }
            }
            if ($canResolve) {
                return $zone;
            }
        }

        // Default to discard if no conditions are met
        return ZONE_DISCARD;
    }
}
