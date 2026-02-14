import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface RewardCardSelectionArgs {
   selectableCards: Card[];
   card: Card;
   nbr: number;
}

export class RewardCardSelectionState extends BaseState<RewardCardSelectionArgs> {
   onEnteringState(args: RewardCardSelectionArgs, isCurrentPlayerActive: boolean): void {
      this.game.cardManager.setCardAsSelected(args.card);

      if (!isCurrentPlayerActive) return;

      const stocks = new Set(args.selectableCards.map((card) => this.game.cardManager.getCardStock(card)!));

      stocks.forEach((stock) => {
         stock.setSelectionMode(args.nbr > 1 ? "multiple" : "single");
         stock.setSelectableCards(args.selectableCards);
         stock.onSelectionChange = () => {
            const selectedCards = this.getSelectedCards(args);
            const btnConfirm = document.getElementById("btn-confirm")! as HTMLButtonElement;
            btnConfirm.disabled = selectedCards.length !== args.nbr;
         };
      });

      // Confirm button
      const handleConfirm = async () => {
         const selectedCards = this.getSelectedCards(args);
         const cardIds = selectedCards.map((card) => card.id);
         this.game.closeDiscardPopupIfNeeded();
         await this.game.actions.performAction("actCardSelection", { cardIds });
      };

      this.game.statusBar.addActionButton(_("Confirm"), handleConfirm, {
         disabled: true,
         id: "btn-confirm",
      });
   }

   private getSelectedCards(args: RewardCardSelectionArgs): Card[] {
      const stocks = new Set(args.selectableCards.map((card) => this.game.cardManager.getCardStock(card)!));
      let selectedCards: Card[] = [];
      stocks.forEach((stock) => {
         if (!stock) return;
         selectedCards = selectedCards.concat(stock.getSelection());
      });
      return selectedCards;
   }
}
