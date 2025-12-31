<?php

use Bga\Games\StarWarsDeckbuilding\Ability\AbilityFactory;

class CardInstance
{
    public int $id;
    public int $typeArg;
    public string $location;
    public int $locationArg;
    public string $name;
    public string $type;
    public string $faction;
    public bool $unique;
    public int $img;
    public int $cost;
    public int $power;
    public int $force;
    public int $resource;
    public array $abilities;

    public function __construct(
        int $id,
        int $typeArg,
        string $location,
        int $locationArg,
        string $name,
        string $type,
        string $faction,
        bool $unique,
        int $img,
        int $cost,
        int $power,
        int $force,
        int $resource,
        array $abilities
    ) {
        $this->id = $id;
        $this->typeArg = $typeArg;
        $this->location = $location;
        $this->locationArg = $locationArg;
        $this->name = $name;
        $this->type = $type;
        $this->faction = $faction;
        $this->unique = $unique;
        $this->img = $img;
        $this->cost = $cost;
        $this->power = $power;
        $this->force = $force;
        $this->resource = $resource;
        $this->abilities = $abilities;
    }

    /** @return Ability[] */
    public function getAbilities(): array
    {
        /** @var Ability[] */
        $abilities = [];
        foreach ($this->abilities as $abilityData) {
            $abilities[] = AbilityFactory::create($abilityData);
        }
        return $abilities;
    }

    public function getOnlyId() : CardInstance {
        return new CardInstance(
            id: $this->id,
            typeArg: 0,
            location: '',
            locationArg: 0,
            name: '',
            type: '',
            faction: '',
            unique: false,
            img: 0,
            cost: 0,
            power: 0,
            force: 0,
            resource: 0,
            abilities: [],
        );
    }
}
