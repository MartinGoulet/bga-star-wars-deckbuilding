import { Game } from "./game";
import { Card } from "./types/game";

export class NotificationManager {
   constructor(private game: Game) {}

   setup() {
      this.game.notifications.setupPromiseNotifications({ handlers: [this], logger: console.log });

      const getNotifs = (): string[] => {
         return Object.getOwnPropertyNames(Object.getPrototypeOf(this))
            .filter((prop) => prop.startsWith("notif_") && typeof (this as any)[prop] === "function")
            .map((prop) => prop.slice(6));
      };

      // ["message", ...getNotifs()].forEach((eventName) => {
      //    (this.game.gameui as any).notifqueue.setIgnoreNotificationCheck(
      //       eventName,
      //       (notif: any) =>
      //          notif.args.excluded_player_id && notif.args.excluded_player_id == this.game.players.getCurrentPlayerId()
      //    );
      // });
   }

   private async notif_onPlayCard(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.playArea.addCard(args.card);
   }

   private async notif_onPlayCardToShipArea(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.ships.addCard(args.card, {
         parallelAnimations: [
            {
               keyframes: [{ transform: "rotate(90deg)" }],
            },
         ],
         duration: 500,
      });
   }

   private async notif_setPlayerCounter(args: any) {
      // debugger;
   }

   private notif_setTableCounter(args: TableCounterNotificationArgs) {
      if (args.name === "force") {
         this.game.tableCenter.setForceCounter(args.value);
      }
   }

   private async notif_onPurchaseGalaxyCard(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.discard.addCard(args.card);
      await this.game.gameui.wait(350);
   }

   private async notif_onMoveCardToHand(args: { player_id: number; card: Card }) {
      if (args.player_id !== this.game.players.getCurrentPlayerId()) return;
      await this.game.playerHand.addCard(args.card);
      await this.game.gameui.wait(350);
   }

   private async notif_onDrawCards(args: { player_id: number; cards: Card[]; _private?: { cards: Card[] } }) {
      const table = this.game.getPlayerTable(args.player_id);
      const addedCards = args._private?.cards ?? args.cards;
      if (args.player_id === this.game.players.getCurrentPlayerId()) {
         await this.game.playerHand.addCards(addedCards, { fromStock: table.deck }, true);
      } else {
         await table.deck.removeCards(addedCards, {
            slideTo: this.game.playerPanels.getElement(args.player_id),
            autoUpdateCardNumber: true,
         });
      }
      await this.game.gameui.wait(350);
   }

   private notif_onDealDamageToCard(args: { player_id: number; card: Card }) {
      this.game.cardManager.setDamageOnCard(args.card);
   }

   private notif_onRepairDamageBase(args: { player_id: number; card: Card }) {
      this.game.cardManager.setDamageOnCard(args.card);
   }

   private async notif_onDiscardCards(args: { player_id: number; cards: Card[]; destination: string }) {
      // Sort cards by locationArg to discard in the correct order
      const cards = args.cards.sort((a, b) => a.locationArg - b.locationArg);

      switch (args.destination) {
         case "player_discard":
            const table = this.game.getPlayerTable(args.player_id);
            await table.discard.addCards(cards, {}, 200);
            break;
         case "galaxy_discard":
            const galaxyDiscard = this.game.tableCenter.galaxyDiscard;
            await galaxyDiscard.addCards(cards, {}, 200);
            break;
         default:
            debugger;
            this.game.dialogs.showMessage("Unknown destination for discarding cards: " + args.destination, "error");
            return;
      }

      await this.game.gameui.wait(350);
   }

   private async notif_onShuffleDiscardIntoDeck(args: { player_id: number }) {
      const table = this.game.getPlayerTable(args.player_id);
      const cards = table.discard.getCards().map((card) => ({ id: card.id }) as Card);
      await table.deck.addCards(cards);
      await table.deck.shuffle();
   }

   private async notif_onRefillGalaxyRow(args: { newCards: Card[] }) {
      const fromStock = this.game.tableCenter.galaxyDeck;
      await this.game.tableCenter.galaxyRow.addCards(args.newCards, { fromStock }, 300);
   }

   private async notif_onDiscardGalaxyCard(args: { player_id: number; card: Card }) {
      await this.game.tableCenter.galaxyDiscard.addCard(args.card);
      await this.game.gameui.wait(350);
   }

   private async notif_onExileCard(args: { player_id: number; card: Card }) {
      const stock = this.game.cardManager.getCardStock(args.card);
      if (stock) {
         const slideTo = (this.game.tableCenter.galaxyDiscard as any).element as HTMLElement;
         await stock.removeCard(args.card, { slideTo });
      }
   }
   private async notif_onNewBase(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.activeBase.addCard(args.card);
      await this.game.gameui.wait(350);
   }
   private async notif_onMoveCardToTopOfDeck(args: { player_id: number; card: Card; destination: string }) {
      switch (args.destination) {
         case "player_deck":
            const table = this.game.getPlayerTable(args.player_id);
            const card = { ...args.card };
            delete (card as any).img;
            await table.deck.addCard(card, { finalSide: "back", initialSide: "front" });
            await this.game.gameui.wait(350);
            break;
         default:
            this.game.dialogs.showMessage(
               "Unknown destination for moving card to top of deck: " + args.destination,
               "error",
            );
            break;
      }
   }

   private async notif_onMoveCardToDiscard(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.discard.addCard(args.card);
      await this.game.gameui.wait(350);
   }

   private async notif_onMoveCardToGalaxyDiscard(args: { player_id: number; card: Card }) {
      await this.game.tableCenter.galaxyDiscard.addCard(args.card);
      await this.game.gameui.wait(350);
   }

   private async notif_onRevealTopCard(args: { player_id: number; card: Card; from: string }) {
      switch (args.from) {
         case "deck":
            const deck = this.game.tableCenter.galaxyDeck;
            deck.setCardVisible(args.card, false, { updateFront: true, updateFrontDelay: 0 });
            deck.flipCard(args.card);
            await this.game.gameui.wait(2000);
            break;
         default:
            this.game.dialogs.showMessage("Unknown zone for revealing card: " + args.from, "error");
            break;
      }
   }

   private async notif_onMoveCardToGalaxyRow(args: { card: Card }) {
      await this.game.tableCenter.galaxyRow.addCard(args.card);
   }

   private async notif_onMoveCardToGalaxyDeck(args: { card: Card }) {
      const deck = this.game.tableCenter.galaxyDeck;
      const card = { ...args.card };
      delete (card as any).img;
      await deck.addCard(card, { finalSide: "back", initialSide: "front" });
      await this.game.gameui.wait(350);
   }

   private async notif_onHideCards(args: { cardIds: number[] }) {
      await this.game.gameui.wait(2000);
      for (const cardId of args.cardIds) {
         const cardTemp = { id: cardId } as Card;
         const stock = this.game.cardManager.getCardStock(cardTemp);
         stock.setCardVisible(cardTemp, false, { updateFront: true, updateFrontDelay: 0 });
      }
   }
}

interface TableCounterNotificationArgs {
   name: string;
   value: number;
   oldValue: number;
   inc: number;
   absInc: number;
   playerId: number;
   player_name: string;
}
