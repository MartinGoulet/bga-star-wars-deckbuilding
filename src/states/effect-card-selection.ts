import { Card } from "../types/game";
import { BaseState } from "./base-state";
import { BgaCards } from "../libs";

interface EffectCardSelectionArgs {
   card: Card;
   nbr: number;
   optional: boolean;
   selectableCards: Card[];
   target: string;
   description: string;
   descriptionMyTurn: string;
}

export class EffectCardSelectionState extends BaseState<EffectCardSelectionArgs> {
   onEnteringState(args: EffectCardSelectionArgs, isCurrentPlayerActive: boolean): void {
      this.game.cardManager.setCardAsSelected(args.card);

      this.displayDescription(args, isCurrentPlayerActive);

      if (!isCurrentPlayerActive) return;

      const stocks = this.getStocks(args);

      stocks.forEach((stock) => {
         stock.setSelectionMode(args.nbr > 1 ? "multiple" : "single");
         stock.setSelectableCards(args.selectableCards);
         stock.onSelectionChange = () => {
            const selectedCards = this.getSelectedCards(args);
            const btnConfirm = document.getElementById("btn-confirm")! as HTMLButtonElement;
            if(args.optional) {
               btnConfirm.disabled = selectedCards.length > args.nbr;
            } else {
               btnConfirm.disabled = selectedCards.length !== args.nbr;
            }
         };
      });
   }

   onPlayerActivationChange(args: EffectCardSelectionArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;
      this.displayDescription(args, isCurrentPlayerActive);
      this.addConfirmButton(args);
   }

   private addConfirmButton(args: EffectCardSelectionArgs): void {

      const handleConfirm = async () => {
         const selectedCards = this.getSelectedCards(args);
         await this.game.actions.performAction("actCardSelection", {
            cardIds: selectedCards.map((card) => card.id),
         });
      };
      this.game.statusBar.addActionButton(_("Confirm"), handleConfirm, {
         disabled: !args.optional,
         id: "btn-confirm",
      });
   }

   private displayDescription(args: EffectCardSelectionArgs, isCurrentPlayerActive: boolean): void {
      if (isCurrentPlayerActive) {
         this.game.statusBar.setTitle(args.descriptionMyTurn, args)
      } else {
         this.game.statusBar.setTitle(args.description, args)
      }
   }

   private getSelectedCards(args: EffectCardSelectionArgs): Card[] {
      const stocks = this.getStocks(args);
      let selectedCards: Card[] = [];
      stocks.forEach((stock) => {
         if (!stock) return;
         selectedCards = selectedCards.concat(stock.getSelection());
      });
      return selectedCards;
   }

   private getStocks(args: EffectCardSelectionArgs): InstanceType<typeof BgaCards.CardStock<Card>>[] {
      return Array.from(
         new Set(
            args.selectableCards.map((card) => {
               return this.game.cardManager.getCardStock(card)!;
            }),
         ),
      );
   }
}
