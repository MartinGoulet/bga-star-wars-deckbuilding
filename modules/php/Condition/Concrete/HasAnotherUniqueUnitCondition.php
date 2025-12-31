<?php

// namespace Bga\Games\StarWarsDeckbuilding\Condition;

// use CardInstance;

// final class HasAnotherUniqueUnitCondition implements Condition {
//     public function isSatisfied(
//         GameContext $ctx,
//         CardInstance $source
//     ): bool {
//         $units = $ctx->cards()->getUnitsInPlay(
//             $ctx->currentPlayerId()
//         );

//         foreach ($units as $unit) {
//             $isAnotherUniqueUnit = $unit->isUnique()
//                 && $unit->cardId() !== $source->getId();
//             if ($isAnotherUniqueUnit) {
//                 return true;
//             }
//         }

//         return false;
//     }
// }
