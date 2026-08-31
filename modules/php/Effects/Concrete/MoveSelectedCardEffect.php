<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;

final class MoveSelectedCardEffect extends EffectInstance {
    public function __construct(
        private string $target,
        private string $destination,
        private string $cardRef,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        $cardIds = $ctx->globals->get($this->cardRef) ?? [];
        if (empty($cardIds)) {
            return;
        }

        $cards = $ctx->cardRepository->getCardsByIds($cardIds);

        if ($ctx->isSolo() && $this->target !== TARGET_SELF) {
            $enemy = $ctx->soloEnemy();
            foreach ($cards as $cardToMove) {
                if (in_array($this->destination, [ZONE_DISCARD, ZONE_PLAYER_DISCARD], true)) {
                    $enemy->moveCard($cardToMove, ZONE_SOLO_ENEMY_MUSTER_HIDDEN);
                    $ctx->game->notify->all(
                        'onSoloEnemyCardReturnedToMuster',
                        clienttranslate('The enemy turns a Muster card face down'),
                        [
                            'card' => $cardToMove->getOnlyId(),
                            'destination' => ZONE_SOLO_ENEMY_MUSTER_HIDDEN,
                        ],
                    );
                    continue;
                }

                if (in_array($this->destination, [ZONE_TOP_DECK, ZONE_PLAYER_DECK], true)) {
                    $enemy->moveCard($cardToMove, ZONE_SOLO_ENEMY_MUSTER_HIDDEN);
                    $ctx->game->notify->all(
                        'onSoloEnemyCardReturnedToMuster',
                        clienttranslate('The enemy turns a Muster card face down'),
                        [
                            'card' => $cardToMove->getOnlyId(),
                            'destination' => ZONE_SOLO_ENEMY_MUSTER_HIDDEN,
                        ],
                    );
                    continue;
                }

                if ($this->destination === ZONE_EXILE) {
                    $ctx->cardRepository->addCardToExile($cardToMove->id);
                    $ctx->game->notify->all(
                        'onSoloEnemyCardExiled',
                        clienttranslate('The enemy exiles ${card_name}'),
                        ['card' => $cardToMove],
                    );
                    continue;
                }

                throw new \InvalidArgumentException('Unsupported solo enemy card destination.');
            }
            return;
        }

        $player = $this->target === TARGET_SELF
            ? $ctx->currentPlayer()
            : $ctx->opponentPlayer();

        foreach ($cards as $cardToMove) {
            switch ($this->destination) {
                case ZONE_HAND:
                    $player->moveCardToHand($cardToMove);
                    break;
                case ZONE_DISCARD:
                case ZONE_PLAYER_DISCARD:
                    $player->moveCardToDiscard($cardToMove);
                    break;
                case ZONE_TOP_DECK:
                case ZONE_PLAYER_DECK:
                    $player->moveCardToTopOfDeck($cardToMove);
                    break;
                case ZONE_GALAXY_DISCARD:
                    $player->moveCardToGalaxyDiscard($cardToMove);
                    break;
                case ZONE_GALAXY_ROW:
                    $player->moveCardToGalaxyRow($cardToMove);
                    break;
                case ZONE_GALAXY_DECK:
                    $player->moveCardToGalaxyDeck($cardToMove);
                    break;
                case ZONE_EXILE:
                    $player->moveCardToExile($cardToMove->id);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown destination for MoveSelectedCardEffect: " . $this->destination);
            }
        }
    }
}
