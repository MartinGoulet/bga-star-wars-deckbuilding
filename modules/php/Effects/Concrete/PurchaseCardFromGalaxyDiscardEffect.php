<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_CardSelection;
use Bga\Games\StarWarsDeckbuilding\States\Purchase_Begin;
use BgaUserException;

final class PurchaseCardFromGalaxyDiscardEffect extends EffectInstance implements NeedsPlayerInput
{
    private string $nextState = Effect_CardSelection::class;

    public function __construct(private string $cardRef)
    {
    }

    public function resolve(GameContext $ctx): void
    {
        $cards = $this->getPurchasableCards($ctx);
        if (count($cards) !== 1) {
            return;
        }

        $card = current($cards);
        $ctx->globals->set($this->cardRef, [$card->id]);
        $ctx->globals->set(GVAR_PURCHASE_CARD_ID, $card->id);
        $this->nextState = '';
        $ctx->getGameEngine()->setNextState(Purchase_Begin::class);
    }

    public function getNextState(): string
    {
        return $this->nextState;
    }

    public function getArgs(GameContext $ctx): array
    {
        $playerId = $ctx->currentPlayer()->playerId;

        return [
            'nbr' => 1,
            'optional' => false,
            'selectableCards' => array_values($this->getPurchasableCards($ctx)),
            'card' => $this->sourceCard,
            'target' => TARGET_SELF,
            'player_name' => $ctx->game->getPlayerNameById($playerId),
            'player_id' => $playerId,
            'description' => clienttranslate('${player_name} must select a card to purchase from the Galaxy discard pile'),
            'descriptionMyTurn' => clienttranslate('${you} must select a card to purchase from the Galaxy discard pile'),
        ];
    }

    public function onPlayerChoice(GameContext $ctx, array $choice): string
    {
        $cardIds = $choice['cardIds'] ?? [];
        $cards = $this->getPurchasableCards($ctx);
        $selectableIds = array_map(fn($card) => $card->id, $cards);

        if (count($cardIds) !== 1 || !in_array((int) $cardIds[0], $selectableIds, true)) {
            throw new BgaUserException('You must select one purchasable card from the Galaxy discard pile.');
        }

        $cardId = (int) $cardIds[0];
        $ctx->globals->set($this->cardRef, [$cardId]);
        $ctx->globals->set(GVAR_PURCHASE_CARD_ID, $cardId);

        return Purchase_Begin::class;
    }

    /** @return CardInstance[] */
    private function getPurchasableCards(GameContext $ctx): array
    {
        $player = $ctx->currentPlayer();
        $resources = $ctx->game->playerResources->get($player->playerId);
        $faction = $player->getFaction();

        return array_values(array_filter(
            $ctx->cardRepository->getGalaxyDiscardPile(),
            fn($card) => $card->cost <= $resources
                && in_array($card->faction, [$faction, FACTION_NEUTRAL], true)
        ));
    }
}