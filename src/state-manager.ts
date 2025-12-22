import { Game } from "./game";
import { StateHandler } from "./types/game";
import { debugLog } from "./utils";

export class StateManager {
    private readonly states: { [stateName: string]: StateHandler<any> } = {};

    constructor(private game: Game) { 
        // this.states["PlayerTurn"] = new PlayerTurnStateHandler(this.game);
        // this.states["playerPrediction"] = new PlayerPredictionStateHandler(this.game);
    }

    public onEnteringState(stateName: string, args: any) {
        debugLog("Entering state:", stateName, args);
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
    }

    public onUpdateActionButtons(stateName: string, args: any) {
        debugLog("Updating action buttons for state:", stateName, args);
        const state = this.states[stateName];
        if (state && state.onUpdateActionButtons) {
            state.onUpdateActionButtons(args.args, this.game.players.isCurrentPlayerActive());
        }
    }
}