import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnAttackCommitArgs {
   target: Card;
   attackers: Card[];
}

export class PlayerTurnAttackCommitState extends BaseState<PlayerTurnAttackCommitArgs> {
   onEnteringState(args: PlayerTurnAttackCommitArgs, isCurrentPlayerActive: boolean): void {
      this.game.cardManager.setCardAsSelected(args.target);

      this.addConfirmButton();
      this.addCancelButton();

      const activePlayerId = this.game.players.getActivePlayerId()!;
      const playArea = this.game.getPlayerTable(activePlayerId).playArea;
      const shipArea = this.game.getPlayerTable(activePlayerId).ships;

      playArea.setSelectionMode("multiple");
      playArea.setSelectableCards(args.attackers);
      if (isCurrentPlayerActive) {
         playArea.onSelectionChange = (selection: Card[]) => {
            const btnConfirm = document.getElementById("btn-confirm-attackers")! as HTMLButtonElement;
            btnConfirm.disabled = selection.length === 0;
         };
         args.attackers.forEach((card) => playArea.selectCard(card));
      }

      shipArea.setSelectionMode("multiple");
      shipArea.setSelectableCards(args.attackers);
      if (isCurrentPlayerActive) {
         shipArea.onSelectionChange = (selection: Card[]) => {
            const btnConfirm = document.getElementById("btn-confirm-attackers")! as HTMLButtonElement;
            btnConfirm.disabled = selection.length === 0;
         };
         args.attackers.forEach((card) => shipArea.selectCard(card));
      }

   }

   private addConfirmButton(): void {
      const handleConfirm = async () => {
         const selectedCards = [
            ...this.game.getCurrentPlayerTable().playArea.getSelection(),
            ...this.game.getCurrentPlayerTable().ships.getSelection(),
         ];
         const cardIds = selectedCards.map((card) => card.id);
         await this.game.actions.performAction("actCommitAttack", { cardIds });
      };
      this.game.statusBar.addActionButton(_("Confirm Attackers"), handleConfirm, {
         disabled: true,
         id: "btn-confirm-attackers",
      });
   }

   private addCancelButton(): void {
      const handleCancel = async () => this.game.actions.performAction("actCancel");
      this.game.statusBar.addActionButton(_("Cancel"), handleCancel, {
         color: "alert",
      });
   }
}
