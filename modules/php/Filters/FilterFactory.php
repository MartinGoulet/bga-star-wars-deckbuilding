<?php

namespace Bga\Games\StarWarsDeckbuilding\Filters;

use Bga\Games\StarWarsDeckbuilding\Filters\Concrete\HasCardTypeFilter;

final class FilterFactory {

    public static function createFilter(array $filter): FilterInstance {
        return match ($filter['type']) {
            FILTER_HAS_CARD_TYPE => new HasCardTypeFilter($filter['cardTypes']),
            default => throw new \InvalidArgumentException("Unknown filter type: {$filter['type']}"),
        };
    }
}