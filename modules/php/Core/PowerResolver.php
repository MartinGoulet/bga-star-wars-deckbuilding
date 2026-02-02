<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\Db\Globals;
use Bga\Games\StarWarsDeckbuilding\Game;
use CardInstance;

final class PowerResolver {

    private Globals $globals;
    private array $modifiers = [];

    public function __construct(private Game $game) {
        $this->globals = $game->globals;
        $this->modifiers = $this->globals->get(GVAR_ATTACK_MODIFIER_PER_CARDS, []);
    }

    public static function getPlayerTotalPower(int $playerId, GameContext $ctx): int {
        $cardsInPlay = $ctx->cardRepository->getPlayerPlayArea($playerId);

        $cardsAlreadyAttackedIds = Game::get()->globals->get(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
        $cardsInPlay = array_filter($cardsInPlay, fn($card) => !in_array($card->id, $cardsAlreadyAttackedIds));

        $resolver = new PowerResolver(Game::get());
        return $resolver->getPowerOfCards($cardsInPlay);
    }

    /**
     * @param CardInstance[] $cards
     */
    public function getPowerOfCards(array $cards): int {
        $totalPower = 0;
        foreach ($cards as $card) {
            $totalPower += $this->getPowerOfCard($card);
        }
        return $totalPower;
    }

    public function getPowerOfCard(CardInstance $card): int {
        $value = $card->power;

        if(isset($this->modifiers[$card->id])) {
            $value += $this->modifiers[$card->id];
        }

        // TODO Calculate bonuses from abilities, attachments, etc.

        return $value;
    }
}