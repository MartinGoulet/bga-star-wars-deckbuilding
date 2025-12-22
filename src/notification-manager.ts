import { Game } from "./game";

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
            (notif: any) => notif.args.excluded_player_id && notif.args.excluded_player_id == this.game.players.getCurrentPlayerId()
         );
      });
   }
}