---
name: create-bga-backend-state
description: "Use when creating or modifying a Board Game Arena backend PHP state, especially a phase state with GameState, StateType, private player states, transitions, notifications, and PossibleAction methods."
---

# Create a BGA Backend State

Create a PHP state for a Board Game Arena state machine while preserving the game's existing state, notification, service, and framework conventions.

## Workflow

1. Identify the state contract before editing.
    - Find the state identifier, neighboring states, and the relevant phase or state directory in the target game.
   - Read the closest public phase state and its private player state, if one exists.
   - Identify the expected state type: `SINGLE_PLAYER`, `MULTIPLE_ACTIVE_PLAYER`, `PRIVATE`, or another local framework type.
   - List the actions, notifications, globals, counters, services, and repositories that the state must read or mutate.

2. Choose the state split.
   - Use a public `GameState` for work shared by all players, phase notifications, rolls, reveals, scoring, and battle resolution.
   - Use `initialPrivate: SomePlayerState::class` when each player needs an independent choice or follow-up state.
   - Use a dedicated private state when the player needs arguments, actions, validation, or a per-player transition.
   - Do not put a player-specific `PossibleAction` on a public state unless the existing phase pattern explicitly requires it.

3. Build the state declaration.
   - Start every PHP file with `declare(strict_types=1);`.
    - Use the namespace matching the target game's state directory.
    - Extend `GameState` and inject the target game's `Game` class, following the local constructor convention.
   - Call the parent constructor with the state `id`, `type`, a localized `description`, and `initialPrivate` when needed.
   - Reuse an existing trait or service for shared rules instead of copying resolution logic into the state.

4. Implement `onEnteringState()` in execution order.
    - Notify the frontend of the phase with the game's established notification and update phase or round counters when the state owns a boundary.
   - Handle feature branches early, such as an enabled module delegating to a token-choice state.
   - Perform shared rolls, draws, reveals, or calculations through existing domain objects and services.
   - Notify all players using `NotificationMessage` or the established `notify->all` convention, including every value needed by the frontend.
   - Compute the player IDs that actually need a choice.
    - Call `setPlayersMultiactive($playerIds, NextState::class)` and `initializePrivateStateForPlayers($playerIds)` when only some players need private follow-up.
    - Ensure the no-choice path still reaches the correct next state rather than leaving an empty multiactive state.

5. Implement private player states and actions.
   - Expose only the arguments needed by the current player through `getArgs(int $currentPlayerId): array`.
   - In `onEnteringState`, immediately advance players whose arguments show that no choice is required.
   - Mark player actions with `#[PossibleAction]` and match the action parameter types to the BGA framework types.
   - Use `#[IntArrayParam()]` for arrays of integer choices when that is the backend contract.
   - Validate resource amounts, allowed identifiers, ownership, and available inventory before mutating counters.
   - Throw `UserException` for invalid player input and use localized `NotificationMessage` instances for resource changes.
   - Clear one-time globals after a successful choice and advance the current player with `nextPrivateState($currentPlayerId, NextState::class)`.
   - Provide `actPass` only when passing is a valid rule, and send it to the same next private state as the completed action.

6. Preserve state and rule ownership.
   - Keep transitions in the state machine; do not manually invoke another state's action.
    - Use the game's services for business rules and repositories for persistence, following the repository/service boundary.
   - Use player counters for resource mutations so the normal notifications and statistics remain intact.
    - Store temporary per-player choices in clearly namespaced globals such as `pending-choice-$playerId` and remove them when consumed.
   - Keep frontend-facing notification names and payload keys stable unless the TypeScript handler is updated in the same change.
    - Do not reimplement shared game rules when an existing service or trait owns them.

## Public State Template

Replace placeholders with the target game's state contract. Adapt optional phase, round, and counter fields to the game's model:

```php
<?php

declare(strict_types=1);

namespace Bga\Games\YourGame\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\YourGame\Game;

class ExampleState extends GameState
{
    function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_EXAMPLE,
            type: StateType::MULTIPLE_ACTIVE_PLAYER,
            initialPrivate: ExamplePlayer::class,
            description: clienttranslate('Waiting for players to complete the phase'),
        );
    }

    function onEnteringState()
    {
        $this->notify->all('onPhaseStart', clienttranslate('Example phase'), [
            'phase' => 0,
        ]);

        $playerIds = $this->resolvePhaseChoices();
        $this->gamestate->setPlayersMultiactive($playerIds, ExampleEnd::class);
        $this->gamestate->initializePrivateStateForPlayers($playerIds);
    }

    private function resolvePhaseChoices(): array
    {
        return [];
    }
}
```

## Private State Template

Use a private state when players must make a validated choice independently:

```php
<?php

declare(strict_types=1);

namespace Bga\Games\YourGame\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\YourGame\Game;

class ExamplePlayer extends GameState
{
    function __construct(protected Game $game)
    {
        parent::__construct(
            $game,
            id: ST_EXAMPLE_PLAYER,
            type: StateType::PRIVATE,
            description: clienttranslate('Waiting for other players to finish their turn'),
            descriptionMyTurn: clienttranslate('${you} must choose'),
        );
    }

    public function getArgs(int $currentPlayerId): array
    {
        return [
            'amount' => 1,
        ];
    }

    #[PossibleAction]
    public function actChoose(int $choice, int $currentPlayerId): void
    {
        if (!in_array($choice, [1, 2, 3], true)) {
            throw new UserException('Invalid choice selected.');
        }

        $this->game->notify->all('message', new NotificationMessage(
            clienttranslate('${player_name} makes a choice'),
            ['player_id' => $currentPlayerId, 'choice' => $choice],
        ));

        $this->gamestate->nextPrivateState($currentPlayerId, ExampleEnd::class);
    }
}
```

## Decision Points

- If all players continue together, use the existing shared transition instead of creating unnecessary private states.
- If only some players need a choice, pass exactly those IDs to `setPlayersMultiactive`; do not activate every player by default.
- If an optional module changes the phase flow, branch before shared work and return its dedicated state.
- If a game rule is shared by multiple states, place it in the existing service or trait and keep the state as orchestration.
- If an effect is immediate, apply it through the appropriate game counter or service; if it requires a player choice, store the pending value in a per-player global.
- If an action accepts a list of items, validate both the length and each repeated value against the player's current inventory or legal choices.
- If the next state is uncertain, inspect the neighboring state graph and the game's state identifiers before writing the transition.

## Completion Checks

- Every touched PHP file uses strict types, the correct namespace, and the expected `GameState` constructor contract for the target game.
- The state type matches the number of active players and the public/private split matches the player interaction.
- Every transition has a concrete next state, including feature branches and zero-choice paths.
- `setPlayersMultiactive` and `initializePrivateStateForPlayers` receive the same intended player set.
- Possible actions validate input before changing counters or clearing globals.
- Notifications use existing names, translation patterns, and payload keys expected by the frontend.
- Run `php -l` on every touched PHP file and fix syntax errors caused by the change.
- Review the diff for unrelated rule changes, generated files, or formatting churn.