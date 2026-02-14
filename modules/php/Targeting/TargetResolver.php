<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

final class TargetResolver {
    public function __construct(private GameContext $ctx)
    {
    }

    /**
     * @return CardInstance[]
     */
    public function resolve(TargetQuery $query): array {

        $allCards = [];

        foreach ($query->zones as $zone) {
            $cards = $this->getSelectableCards($zone, $query->max);
            $allCards = array_merge($allCards, $cards);
        }

        return $this->applyFilters($allCards, $query->filters);
    }

    public function select(TargetQuery $query): array {
        $candidates = $this->resolve($query);

        if ($query->selectionMode === SELECTION_MODE_RANDOM) {
            shuffle($candidates);
            return array_slice($candidates, 0, $query->max);
        }

        // For PLAYER_CHOICE, the actual selection will be handled in the state that requires player input
        return $candidates;
    }

    /**
     * @param CardInstance[] $cards
     * @param CardFilterInterface[] $filters
     * @return CardInstance[]
     */
    private function applyFilters(array $cards, array $filters): array {
        foreach ($filters as $filter) {
            $cards = array_filter($cards, fn($card) => $filter->matches($card));
        }
        return $cards;
    }

    /** @return CardInstance[] */
    private function getSelectableCards(string $zone, int $count = 0): array {
        switch ($zone) {
            case TARGET_SCOPE_SELF_HAND:
            case TARGET_SCOPE_OPPONENT_HAND:
                $playerId = $this->resolvePlayer($zone);
                return $this->ctx->game->cardRepository->getPlayerHand($playerId);

            case TARGET_SCOPE_SELF_PLAY_AREA:
            case TARGET_SCOPE_OPPONENT_PLAY_AREA:
                $playerId = $this->resolvePlayer($zone);
                return $this->ctx->game->cardRepository->getPlayerPlayArea($playerId);

            case TARGET_SCOPE_SELF_SHIP_AREA:
            case TARGET_SCOPE_OPPONENT_SHIP_AREA:
                $playerId = $this->resolvePlayer($zone);
                return $this->ctx->game->cardRepository->getPlayerShips($playerId);

            case TARGET_SCOPE_SELF_DISCARD:
            case TARGET_SCOPE_OPPONENT_DISCARD:
                $playerId = $this->resolvePlayer($zone);
                return $this->ctx->game->cardRepository->getPlayerDiscardPile($playerId);

            case TARGET_SCOPE_GALAXY_ROW:
                return $this->ctx->game->cardRepository->getGalaxyRow();

            case TARGET_SCOPE_GALAXY_DISCARD:
                return $this->ctx->game->cardRepository->getGalaxyDiscardPile();

            case TARGET_SCOPE_GALAXY_DECK:
                return $this->ctx->game->cardRepository->getGalaxyDeckTopCards($count);
            default:
                die("Invalid zone for selectable cards: $zone");
        }
    }

    private function resolvePlayer(string $target): ?int {
        return match ($target) {
            TARGET_SCOPE_SELF_DISCARD => $this->ctx->currentPlayer()->playerId,
            TARGET_SCOPE_SELF_PLAY_AREA => $this->ctx->currentPlayer()->playerId,
            TARGET_SCOPE_SELF_HAND => $this->ctx->currentPlayer()->playerId,
            TARGET_SCOPE_SELF_SHIP_AREA => $this->ctx->currentPlayer()->playerId,
            TARGET_SCOPE_OPPONENT_DISCARD => $this->ctx->opponentPlayer()->playerId,
            TARGET_SCOPE_OPPONENT_PLAY_AREA => $this->ctx->opponentPlayer()->playerId,
            TARGET_SCOPE_OPPONENT_HAND => $this->ctx->opponentPlayer()->playerId,
            TARGET_SCOPE_OPPONENT_SHIP_AREA => $this->ctx->opponentPlayer()->playerId,
            default => 0,
        };
    }
}
