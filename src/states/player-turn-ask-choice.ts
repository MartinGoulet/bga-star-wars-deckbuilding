import { Card, MultipleActiveStateHandler } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnAskChoiceArgs {
   card: Card;
   options: Record<number, string>;
}

export class PlayerTurnAskChoiceState
   extends BaseState<PlayerTurnAskChoiceArgs>
   implements MultipleActiveStateHandler<PlayerTurnAskChoiceArgs>
{
   onEnteringState(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;
      this.game.cardManager.setCardAsSelected(args.card);
   }

   onPlayerActivationChange(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      Object.entries(args.options).forEach(([optionId, option]) => {
         const handle = async () => {
            await this.game.actions.performAction("actMakeChoice", { choiceId: Number(optionId) });
         };

         this.game.statusBar.addActionButton(_(option), handle);
      });
   }
}
