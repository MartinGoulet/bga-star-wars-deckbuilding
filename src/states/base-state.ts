import { Game } from "../game";
import { StateHandler } from "../types/game";

export abstract class BaseState<T> implements StateHandler<T> {
    constructor(protected game: Game) {}
    abstract onEnteringState(args: T, isCurrentPlayerActive: boolean): void;
    onLeavingState(isCurrentPlayerActive: boolean): void {
        this.game.cardManager.removeAllCardsAsSelected();
        this.game.playerTables.forEach((table) => table.onLeaveState());
        this.game.tableCenter.onLeaveState();

        this.game.playerHand.setSelectionMode("none");
        this.game.playerHand.onCardClick = undefined;
        this.game.playerHand.onSelectionChange = undefined;
    }
}
