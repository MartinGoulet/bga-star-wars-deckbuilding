import { Game } from "./game";
import { Card } from "./types/game";

export class NotificationManager {
   constructor(private game: Game) {}

   setup() {
      this.game.notifications.setupPromiseNotifications({ handlers: [this] });

      const getNotifs = (): string[] => {
         return Object.getOwnPropertyNames(Object.getPrototypeOf(this))
            .filter((prop) => prop.startsWith("notif_") && typeof (this as any)[prop] === "function")
            .map((prop) => prop.slice(6));
      };

      ["message", ...getNotifs()].forEach((eventName) => {
         (this.game.gameui as any).notifqueue.setIgnoreNotificationCheck(
            eventName,
            (notif: any) =>
               notif.args.excluded_player_id && notif.args.excluded_player_id == this.game.players.getCurrentPlayerId()
         );
      });
   }

   private async notif_onPlayCard(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.playArea.addCard(args.card);
      this.game.gameui.wait(350);
   }

   public async notif_onPlayerCounter(args: any) {
      // debugger;
   }

   public async notif_onPurchaseGalaxyCard(args: { player_id: number; card: Card }) {
      const table = this.game.getPlayerTable(args.player_id);
      await table.discard.addCard(args.card);
      this.game.gameui.wait(350);
   }

   public async notif_onMoveCardToHand(args: { player_id: number; card: Card }) {
      if (args.player_id !== this.game.players.getCurrentPlayerId()) return;
      await this.game.playerHand.addCard(args.card);
      this.game.gameui.wait(350);
   }

   public async notif_onDrawCards(args: { player_id: number; cards: Card[]; _private?: { cards: Card[] } }) {
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
      this.game.gameui.wait(350);
   }
}
