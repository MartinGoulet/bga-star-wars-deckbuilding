import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface StateArgs {
   ships: Card[];
   opponentId: number;
}

export class PlayerTurnActionResolveDamageShipBaseState extends BaseState<StateArgs> {
   onEnteringState(args: StateArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      const ships = this.game.getPlayerTable(args.opponentId).ships;
      ships.setSelectionMode("single");
      ships.setSelectableCards(args.ships);
      ships.onSelectionChange = (selection: Card[]) => {
         const btnConfirm = document.getElementById("btn-confirm")! as HTMLButtonElement;
         btnConfirm.disabled = selection.length !== 1;
      };

      this.game.statusBar.removeActionButtons();
      this.game.statusBar.addActionButton(_("Confirm"), async () => {
         const selectedShip = ships.getSelection().pop()!;
         await this.game.actions.performAction("actSelectShipToDealDamage", {
            cardId: selectedShip.id,
         });
      }, {
         disabled: true,
         id: "btn-confirm",
      });
   }
}
