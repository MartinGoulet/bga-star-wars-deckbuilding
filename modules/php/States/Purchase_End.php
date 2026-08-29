<?php
declare(strict_types=1);

namespace Bga\Games\StarWarsDeckbuilding\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Game;

class Purchase_End extends GameState
{
    function __construct(protected Game $game) {
        parent::__construct($game,
            id: ST_PURCHASE_END,
            type: StateType::GAME,
        );
    }

    public function getArgs(): array
    {
        return ['_no_notify' => true];
    }

    function onEnteringState() {

        $ctx = new GameContext($this->game);

        $card = $this->game->cardRepository->getCard($this->globals->get(GVAR_PURCHASE_CARD_ID));
        $destinations = $this->globals->get(GVAR_PURCHASE_DESTINATIONS, []);
        $destination = $destinations[0]['destination'] ?? ZONE_DISCARD;

        if (in_array($card->location, [ZONE_GALAXY_ROW, ZONE_GALAXY_DISCARD, ZONE_OUTER_RIM_DECK])) {
            if ($destination === ZONE_TOP_DECK) {
                $ctx->currentPlayer()->moveCardToTopOfDeck($card);
            } else {
                $ctx->currentPlayer()->discardCards([$card->id]);
            }
        }

        $overrides = $this->globals->get(GVAR_PURCHASE_OPTION_OVERRIDES, []);
        $overrides = array_values(array_filter(
            $overrides,
            fn($override) => ($override['expires'] ?? null) !== 'after_next_purchase'
        ));
        $this->globals->set(GVAR_PURCHASE_OPTION_OVERRIDES, $overrides);

        $this->globals->set(GVAR_PURCHASE_DESTINATIONS, []);

        $ctx->refillGalaxyRow();

        return PlayerTurn_ActionSelection::class;
    }
    
}
