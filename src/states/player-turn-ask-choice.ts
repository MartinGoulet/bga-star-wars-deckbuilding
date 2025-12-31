import { Game } from "../game";
import { Card, StateHandler } from "../types/game";

interface PlayerTurnAskChoiceArgs {
   card: Card;
   options: Record<number, string>;
}

export class PlayerTurnAskChoiceState implements StateHandler<PlayerTurnAskChoiceArgs> {
   constructor(protected game: Game) {}
   onEnteringState(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
      this.game.cardManager.setCardAsSelected(args.card);
   }
   onLeavingState(isCurrentPlayerActive: boolean): void {}
   onUpdateActionButtons?(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
        if (!isCurrentPlayerActive) return;

        Object.entries(args.options).forEach(([optionId, option]) => {
            const handle = async () => {
                await this.game.actions.performAction("actMakeChoice", { choiceId: Number(optionId) });
            };

            this.game.statusBar.addActionButton(_(option), handle);
        }
   }
}
