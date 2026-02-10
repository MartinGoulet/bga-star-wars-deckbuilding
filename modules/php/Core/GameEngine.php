<?php

namespace Bga\Games\StarWarsDeckbuilding\Core;

use Bga\GameFramework\Db\Globals;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\Game;
use Bga\Games\StarWarsDeckbuilding\States\PlayerTurn_ActionSelection;
use BgaVisibleSystemException;
use CardInstance;

final class GameEngine {

   private Globals $globals;

   public function __construct(private Game $game, private GameContext $context) {
      $this->globals = $this->game->globals;
   }

   public function addCardEffect(CardInstance $cardInstance, string $trigger): void {

      if ($trigger === TRIGGER_REWARD) {
         $trigger = [
            'effects' => $cardInstance->rewards
         ];
         if (empty($trigger['effects']) && $cardInstance->type !== CARD_TYPE_SHIP) {
            throw new BgaVisibleSystemException("No reward effects defined for card id " . $cardInstance->id);
         }
      } else {
         $trigger = array_find(
            $cardInstance->abilities,
            fn($ability) => $ability['trigger'] === $trigger
         );
         if (empty($trigger)) return;
      }


      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      foreach ($trigger['effects'] as $effect) {
         $effects[] = array_merge(
            $effect,
            ['sourceCardId' => $cardInstance->id]
         );
      }
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
   }

   public function insertEffectsAfterCurrentEffect(CardInstance $card, array $effectsToInsert): void {
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      $currentEffect = array_shift($effects);
      $effectsToInsert = array_map(
         fn($effect) => array_merge($effect, ['sourceCardId' => $card->id]),
         $effectsToInsert
      );
      $effects = array_merge(
         [$currentEffect],
         $effectsToInsert,
         $effects
      );
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
   }

   public function addEffect(EffectInstance $effectInstance): void {
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      $effects[] = $effectInstance->definition;
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
   }

   public function addChoiceEffect(CardInstance $cardInstance, string $target, array $options): void {
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      $effects[] = [
         'type' => EFFECT_CHOICE,
         'sourceCardId' => $cardInstance->id,
         'target' => $target,
         'options' => $options
      ];
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
   }

   public function addMoveCardEffect(CardInstance $cardInstance, string $target, string $destination): void {
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      $effects[] = [
         'type' => EFFECT_MOVE_CARD,
         'sourceCardId' => $cardInstance->id,
         'target' => $target,
         'destination' => $destination
      ];
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
   }

   /** @return string Return next state */
   public function run(): string {

      /** @var array */
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);

      while (count($effects) > 0) {
         $effectDef = current($effects);
         /** @var EffectInstance $effectInstance */
         $effectInstance = EffectFactory::createEffectInstance($effectDef);

         if ($effectInstance->canResolve($this->context) === false) {
            array_shift($effects);
            $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
            continue;
         }

         $effectInstance->resolve($this->context);

         if ($effectInstance instanceof NeedsPlayerInput) {
            return $effectInstance->getNextState();
         } else {
            $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
            array_shift($effects);
            $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);
         }
         $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE, []);
      }

      $this->context->refillGalaxyRow();
      return PlayerTurn_ActionSelection::class;
   }

   /**
    * @return string Return next state
    */
   public function resume(array $data): string {
      /** @var array */
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE);
      $effectDef = array_shift($effects);
      $this->globals->set(GVAR_EFFECTS_TO_RESOLVE, $effects);

      /** @var EffectInstance&NeedsPlayerInput $effectInstance */
      $effectInstance = EffectFactory::createEffectInstance($effectDef);
      $nextState = $effectInstance->onPlayerChoice($this->context, $data);

      if ($nextState !== '') {
         return $nextState;
      }

      return $this->run();
   }

   public function getCurrentEffect(): EffectInstance {
      /** @var array */
      $effects = $this->globals->get(GVAR_EFFECTS_TO_RESOLVE);

      $effectDef = current($effects);
      /** @var EffectInstance&NeedsPlayerInput $effectInstance */
      $effectInstance = EffectFactory::createEffectInstance($effectDef);

      return $effectInstance;
   }
}
