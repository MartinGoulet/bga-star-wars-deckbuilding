<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\Solo\SoloEnemyContext;
use Bga\Games\StarWarsDeckbuilding\States\Effect_Choice;
use CardIds;

final class ChoiceOptionEffect extends EffectInstance implements NeedsPlayerInput {
    private string $nextState = Effect_Choice::class;

    public function __construct(
        public string $target,
        public array $options,
    ) {
    }

    public function resolve(GameContext $ctx): void {
        if ($ctx->isSolo() && $this->target !== TARGET_SELF) {
            $this->queueOption($ctx, $this->getSoloChoice($ctx));
            $this->nextState = '';
        }
    }

    public function getNextState(): string {
        return $this->nextState;
    }

    public function onPlayerChoice(GameContext $ctx, array $data): string {
        $choice = $data['choice'];
        $this->queueOption($ctx, $choice);
        return '';
    }

    private function queueOption(GameContext $ctx, int $choice): void {
        if (!isset($this->options[$choice])) {
            throw new \InvalidArgumentException("Invalid choice id: $choice");
        }

        $option = $this->options[$choice];
        $option['sourceCardId'] = $this->sourceCard->id;
        $effect = EffectFactory::createEffectInstance($option);
        $ctx->getGameEngine()->addEffect($effect);
    }

    private function getSoloChoice(GameContext $ctx): int {
        if ($this->sourceCard->typeArg === CardIds::DUROS_SPY) {
            return $ctx->soloEnemy()->isForceFullyWithEnemy() && !empty(
                $ctx->soloEnemy()->getCards(ZONE_SOLO_ENEMY_MUSTER_VISIBLE)
            ) ? 0 : 1;
        }

        if ($this->sourceCard->typeArg === CardIds::B_WING) {
            $enemy = new SoloEnemyContext($ctx->game);
            $remainingBases = count($enemy->getCards(ZONE_SOLO_ENEMY_BASES))
                + ($enemy->getActiveBase() === null ? 0 : 1);
            return $remainingBases === 1 ? 0 : 1;
        }

        return 0;
    }

    public function getArgs(GameContext $context): array {

        $options = array_filter($this->options, function($o) use ($context) {
            if(!isset($o['conditions'])) return true;
            $conditions = ConditionFactory::createConditions($this->sourceCard, $o['conditions']);
            if($conditions === null) return true;
            foreach($conditions as $condition) {
                if(!$condition->isSatisfied($context)) {
                    return false;
                }
            }
            return true;
        });

        $options = array_map(fn($o) => ['label' => $o['label'], 'labelArgs' => $o['labelArgs'] ?? []], $options);

        $target = $this->target === TARGET_SELF
            ? $context->currentPlayer()->playerId
            : $context->getOpponentId();

        $data = [
            'options' => $options,
            'card' => $this->sourceCard,
            'target' => $target,
        ];

        return $data;
    }
}
