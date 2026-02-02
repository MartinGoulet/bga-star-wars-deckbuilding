<?php

namespace Bga\Games\StarWarsDeckbuilding\Effects\Concrete;

use Bga\Games\StarWarsDeckbuilding\Core\GameContext;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectFactory;
use Bga\Games\StarWarsDeckbuilding\Effects\EffectInstance;
use Bga\Games\StarWarsDeckbuilding\Effects\NeedsPlayerInput;
use Bga\Games\StarWarsDeckbuilding\States\Effect_Choice;

final class ChoiceEffect extends EffectInstance implements NeedsPlayerInput {

    public function __construct(
        public string $target,
        public array $options,
    ) {
    }

    public function resolve(GameContext $ctx): void {
    }

    public function getNextState(): string {
        return Effect_Choice::class;
    }

    public function onPlayerChoice(GameContext $ctx, array $data): string {
        $choice = $data['choice'];
        $option = $this->options[$choice];
        $option['sourceCardId'] = $this->sourceCard->id;
        $effect = EffectFactory::createEffectInstance($option);
        $ctx->getGameEngine()->addEffect($effect);
        return '';
    }

    public function getArgs(GameContext $context): array {

        $options = array_map(fn($o) => $o['label'], $this->options);
        
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
