<?php

use Bga\Games\StarWarsDeckbuilding\Condition\Condition;
use Bga\Games\StarWarsDeckbuilding\Condition\ConditionFactory;
use Bga\Games\StarWarsDeckbuilding\Core\GameContext;

class CardInstance {
    public function __construct(
        public int $id,
        public int $typeArg,
        public string $location,
        public int $locationArg,
        public string $name,
        public string $gametext,
        public string $type,
        public string $faction,
        public bool $unique,
        public int $img,
        public int $cost,
        public int $power,
        public int $force,
        public int $resource,
        public int $damage,
        public int $health,
        public array $abilities,
        public array $rewards,
        public array $traits,
    ) {
    }

    public function hasPlayableAbility(GameContext $ctx): bool {
        $effects = $this->getEffect(TRIGGER_ACTIVATE_CARD, $ctx);
        return !empty($effects);
    }

    public function getEffect(string $trigger, GameContext $ctx): array {
        $abilities = $this->abilities;

        if (empty($abilities)) {
            return [];
        }

        $trigger = array_find($abilities, fn($ability) => isset($ability['trigger']) && $ability['trigger'] === $trigger);

        if ($trigger === null) {
            return [];
        }

        $conditions = $trigger['conditions'] ?? [];
        $conditions = ConditionFactory::createConditions($this, $conditions);
        $canResolve = true;
        foreach ($conditions as $abilityConditions) {
            if (!$abilityConditions->isSatisfied($ctx)) {
                $canResolve = false;
                break;
            }
        }

        if (!$canResolve) {
            return [];
        }

        return $trigger['effects'] ?? [];
    }

    public function isOwnedBy(int $playerId): bool {
        $locationEnd = explode('_', $this->location);
        $locationEnd = end($locationEnd);
        return $this->locationArg === $playerId ||
            $locationEnd === (string)$playerId;
    }

    public function getOnlyId(): CardInstance {
        return new CardInstance(
            id: $this->id,
            typeArg: 0,
            location: '',
            locationArg: 0,
            name: '',
            gametext: '',
            type: '',
            faction: '',
            unique: false,
            img: 0,
            cost: 0,
            power: 0,
            force: 0,
            resource: 0,
            damage: 0,
            health: 0,
            abilities: [],
            rewards: [],
            traits: [],
        );
    }

    public function getUI(): array {
        return [
            'id' => $this->id,
            'location' => $this->location,
            'locationArg' => $this->locationArg,
        ];
    }
}
