import { BaseState } from "./base-state";

interface PlayerTurnStartTurnBaseArgs {}

export class PlayerTurnStartTurnBaseState extends BaseState<PlayerTurnStartTurnBaseArgs> {
   public onEnteringState(args: PlayerTurnStartTurnBaseArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      
   }
}
