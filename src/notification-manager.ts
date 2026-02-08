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
      await table.ships.addCard(args.card);
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
         await this.game.playerHand.addCards(addedCards, { fromStock: table.deck });
      } else {
         await table.deck.removeCards(addedCards, {
            slideTo: this.game.playerPanels.getElement(args.player_id),
            autoUpdateCardNumber: true,
         });
      }
      await this.game.gameui.wait(350);
   }

   public notif_onDealDamageToCard(args: { player_id: number; card: Card }) {
      this.game.cardManager.setDamageOnCard(args.card);
   }

   public notif_onRepairDamageBase(args: { player_id: number; card: Card }) {
      this.game.cardManager.setDamageOnCard(args.card);
   }

   private async notif_onDiscardCards(args: { player_id: number; cards: Card[] }) {
      const table = this.game.getPlayerTable(args.player_id);
      // Sort cards by locationArg to discard in the correct order
      const cards = args.cards.sort((a, b) => a.locationArg - b.locationArg);
      await table.discard.addCards(cards, {}, 200);
      await this.game.gameui.wait(350);
   }

   private async notif_onShuffleDiscardIntoDeck(args: { player_id: number }) {
      const table = this.game.getPlayerTable(args.player_id);
      const cards = table.discard.getCards().map((card) => ({ id: card.id } as Card));
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
         const slideTo = (this.game.tableCenter.galaxyDiscard as any).element as HTMLElement
         await stock.removeCard(args.card, { slideTo });
      }
   }
   private async notif_onNewBase(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.activeBase.addCard(args.card);
      await this.game.gameui.wait(350);
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