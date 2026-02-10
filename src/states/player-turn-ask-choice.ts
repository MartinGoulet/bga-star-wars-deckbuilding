import { Card, MultipleActiveStateHandler } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnAskChoiceArgs {
   card: Card;
   options: Record<number, LabelOption>;
}

interface LabelOption {
   label: string;
   labelArgs?: Record<string, any>;
}

export class PlayerTurnAskChoiceState
   extends BaseState<PlayerTurnAskChoiceArgs>
   implements MultipleActiveStateHandler<PlayerTurnAskChoiceArgs>
{
   onEnteringState(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
      this.game.cardManager.setCardAsSelected(args.card);
   }

   onPlayerActivationChange(args: PlayerTurnAskChoiceArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      Object.entries(args.options).forEach(([optionId, option]) => {
         const handle = async () => {
            await this.game.actions.performAction("actMakeChoice", { choiceId: Number(optionId) });
         };

         const label = this.game.gameui.format_string(option.label, option.labelArgs ?? {});
         this.game.statusBar.addActionButton(label, handle);
      });
   }
}
