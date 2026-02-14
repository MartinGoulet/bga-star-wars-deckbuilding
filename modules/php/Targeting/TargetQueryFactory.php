<?php

namespace Bga\Games\StarWarsDeckbuilding\Targeting;

use Bga\Games\StarWarsDeckbuilding\Targeting\Filtering\FactionsFilter;
use Bga\Games\StarWarsDeckbuilding\Targeting\Filtering\TraitsFilter;
use Bga\Games\StarWarsDeckbuilding\Targeting\Filtering\TypesFilter;

final class TargetQueryFactory
{
    public static function create(array $target): TargetQuery
    {
        $zones = $target['zones'];
        $filters = self::createFilters($target['filters'] ?? []);
        $min = $target['min'] ?? 1;
        $max = $target['max'] ?? 1;
        $selectionMode = isset($target['selectionMode']) ? $target['selectionMode'] : SELECTION_MODE_PLAYER_CHOICE;

        return new TargetQuery($zones, $filters, $min, $max, $selectionMode);
    }

    private static function createFilters(array $filters): array
    {
        return array_map(function (array $filter) {
            return match ($filter['type']) {
                FILTER_CARD_TYPES => new TypesFilter($filter['cardTypes']),
                FILTER_FACTIONS => new FactionsFilter($filter['factions'], $filter['negate'] ?? false),
                FILTER_HAS_TRAIT => new TraitsFilter($filter['traits']),
                default => throw new \InvalidArgumentException("Unknown filter type: {$filter['type']}"),
            };
        }, $filters);
    }
}