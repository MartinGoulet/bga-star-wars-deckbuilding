<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\Db\Globals;
use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetQueryFactory;
use Bga\Games\StarWarsDeckbuilding\Targeting\TargetResolver;
use CardInstance;

final class PowerResolver {

   private Globals $globals;
   private array $modifiers = [];
   private array $cardsInPlay = [];

   public function __construct(private GameContext $ctx) {
      $this->globals = $ctx->globals;
      $this->modifiers = $this->globals->get(GVAR_ATTACK_MODIFIER_PER_CARDS, []);
      $this->cardsInPlay = $this->getCardInPlayForModifier($ctx);
   }

   public static function getPlayerTotalPower(int $playerId, GameContext $ctx): int {

      $target = TargetQueryFactory::create([
         'zones' => [TARGET_SCOPE_SELF_PLAY_AREA, TARGET_SCOPE_SELF_SHIP_AREA],
      ]);
      $cardsInPlay = (new TargetResolver($ctx))->resolve($target);

      $cardsAlreadyAttackedIds = Game::get()->globals->get(GVAR_ALREADY_ATTACKING_CARDS_IDS, []);
      $cardsInPlay = array_filter($cardsInPlay, fn($card) => !in_array($card->id, $cardsAlreadyAttackedIds));

      $resolver = new PowerResolver($ctx);
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

      if (isset($this->modifiers[$card->id])) {
         $value += $this->modifiers[$card->id];
      }

      // TODO Calculate bonuses from abilities, attachments, etc.
      $value += $this->calculateStaticModifiers($card, $this->ctx);

      $value += $this->calculateAuraModifiers($card, $this->ctx);

      return $value;
   }

   private function calculateStaticModifiers(CardInstance $card, GameContext $ctx): int {
      $bonus = 0;

      foreach ($card->abilities ?? [] as $ability) {

         if (($ability['type'] ?? null) !== ABILITY_STATIC_ATTACK_MODIFIER) {
            continue;
         }

         if (!$this->checkConditions($card, $ability['conditions'] ?? [], $ctx)) {
            continue;
         }

         $bonus += $ability['value'] ?? 0;
      }

      return $bonus;
   }

   private function checkConditions(CardInstance $card, array $conditions, GameContext $ctx): bool {
      $conditions = ConditionFactory::createConditions($card, $conditions);
      foreach ($conditions as $conditionInstance) {
         if (!$conditionInstance->isSatisfied($ctx)) {
            return false;
         }
      }
      return true;
   }

   private function calculateAuraModifiers(CardInstance $card, GameContext $ctx): int {
      $bonus = 0;

      foreach ($this->cardsInPlay as $cardInPlay) {
         foreach ($cardInPlay->abilities ?? [] as $ability) {

            if (($ability['type'] ?? null) !== ABILITY_AURA_ATTACK_MODIFIER) {
               continue;
            }

            if (!$this->checkConditions($card, $ability['conditions'] ?? [], $ctx)) {
               continue;
            }

            die('Aura modifiers not implemented yet'); // TODO Implement aura modifiers

            $bonus += $ability['value'] ?? 0;
         }
      }


      return $bonus;
   }

   /**
    * Get all cards in play that should be considered for modifiers (e.g. auras)
    * @return CardInstance[]
    */
   private function getCardInPlayForModifier(GameContext $ctx): array {
      $target = TargetQueryFactory::create([
         'zones' => [TARGET_SCOPE_SELF_PLAY_AREA, TARGET_SCOPE_SELF_SHIP_AREA, TARGET_SCOPE_SELF_BASE],
         'filters' => [
            [
               'type' => FILTER_ABILITIES,
               'abilities' => [ABILITY_AURA_ATTACK_MODIFIER],
            ]
         ],
      ]);
      return (new TargetResolver($ctx))->resolve($target);
   }
}
