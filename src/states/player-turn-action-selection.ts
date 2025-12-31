import { Game } from "../game";
import { Card, StateHandler } from "../types/game";

interface PlayerTurnActionSelectionArgs {
   selectableCardIds: number[];
   selectableGalaxyCardIds: number[];
}

export class PlayerTurnActionSelectionState implements StateHandler<PlayerTurnActionSelectionArgs> {
   constructor(protected game: Game) {}
   onEnteringState(args: PlayerTurnActionSelectionArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      this.setupPlayerHandSelectableCards(args);
      this.setupGalaxyRowSelectableCards(args);
   }
   onLeavingState(isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;
      this.game.playerHand.setSelectionMode("none");
      this.game.playerHand.onCardClick = undefined;
   }
   onUpdateActionButtons?(args: PlayerTurnActionSelectionArgs, isCurrentPlayerActive: boolean): void {
      // this.addButtonPlayCard();
   }
   // private addButtonPlayCard() {
   //    const handle = async () => await this.game.actions.performAction("actPlayCard");

   //    this.game.statusBar.addActionButton(_("Play Card(s)"), handle, {
   //       disabled: this.game.playerHand.getCards().length === 0,
   //    });
   // }

   private setupPlayerHandSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const selectableCards = this.game.playerHand
         .getCards()
         .filter((card) => args.selectableCardIds.includes(card.id));

      this.game.playerHand.setSelectionMode("single");
      this.game.playerHand.setSelectableCards(selectableCards);
      this.game.playerHand.onCardClick = async (card: Card) => {
         if (!args.selectableCardIds.includes(card.id)) return;
         this.game.playerHand.unselectAll(true);
         if ((this.game.gameui as any).isInterfaceLocked()) return;
         await this.game.actions.performAction("actPlayCard", { cardId: card.id });
      };
   }

   private setupGalaxyRowSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const galaxyRow = this.game.tableCenter.galaxyRow;

      const selectableCards = galaxyRow
         .getCards()
         .filter((card) => args.selectableGalaxyCardIds.includes(card.id));

      galaxyRow.setSelectionMode("single");
      galaxyRow.setSelectableCards(selectableCards);
      galaxyRow.onCardClick = async (card: Card) => {
         galaxyRow.unselectCard(card, true);
         await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
      };
   }
}