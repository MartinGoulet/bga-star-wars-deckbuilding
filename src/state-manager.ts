import { Game } from "./game";
import { PlayerTurnActionSelectionState } from "./states/player-turn-action-selection";
import { PlayerTurnAskChoiceState } from "./states/player-turn-ask-choice";
import { StateHandler } from "./types/game";
import { debugLog } from "./utils";

export class StateManager {
    private readonly states: { [stateName: string]: StateHandler<any> } = {};

    constructor(private game: Game) { 
        this.states["playerTurnActionSelection"] = new PlayerTurnActionSelectionState(this.game);
        this.states["playerTurnAskChoice"] = new PlayerTurnAskChoiceState(this.game);
    }

    public onEnteringState(stateName: string, args: any) {
        debugLog("Entering state:", stateName, args.args);
        const state = this.states[stateName];
        if (state && state.onEnteringState) {
            state.onEnteringState(args.args, this.game.players.isCurrentPlayerActive());
        }
    }

    public onLeavingState(stateName: string) {
        debugLog("Leaving state:", stateName);
        const state = this.states[stateName];
        if (state && state.onLeavingState) {
            state.onLeavingState(this.game.players.isCurrentPlayerActive());
        }
        this.game.cardManager.removeAllCardsAsSelected();
    }

    public onUpdateActionButtons(stateName: string, args: any) {
        debugLog("Updating action buttons for state:", stateName, args);
        const state = this.states[stateName];
        if (state && state.onUpdateActionButtons) {
            state.onUpdateActionButtons(args, this.game.players.isCurrentPlayerActive());
        }
    }
}