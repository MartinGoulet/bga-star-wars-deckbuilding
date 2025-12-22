import { BgaAnimations } from "./libs";
import { NotificationManager } from "./notification-manager";
import { StateManager } from "./state-manager";
import { StarWarsGamedatas, StarWarsPlayer } from "./types/game";

interface Game extends Bga {
   onEnteringState: (stateName: string, args: any) => void;
   onLeavingState: (stateName: string) => void;
   onUpdateActionButtons: (stateName: string, args: any) => void;
}

class Game implements Game {
   // @ts-ignore
   public gamedatas: StarWarsGamedatas;
   // @ts-ignore
   private notificationManager: NotificationManager;

   public animationManager: InstanceType<typeof BgaAnimations.Manager>;
   public stateManager: StateManager;


   constructor(bga: Bga<StarWarsGamedatas>) {
      Object.assign(this, bga);

      this.gameArea.getElement().insertAdjacentHTML(
         "beforeend",
         `<div class="swd-table-wrapper">
          </div>`
      );

      this.animationManager = new BgaAnimations.Manager({
         animationsActive: () => this.gameui.bgaAnimationsActive(),
      });

      this.stateManager = new StateManager(this);

      this.onEnteringState = this.stateManager.onEnteringState.bind(this.stateManager);
      this.onLeavingState = this.stateManager.onLeavingState.bind(this.stateManager);
      this.onUpdateActionButtons = this.stateManager.onUpdateActionButtons?.bind(this.stateManager);
   }

   public setup(gamedatas: StarWarsGamedatas) {
      this.gamedatas = gamedatas;

      this.setupNotifications();
   }

   public getOrderedPlayers(): StarWarsPlayer[] {
      const players = Object.values(this.gamedatas.players).sort((a, b) => a.playerNo - b.playerNo);
      const playerIndex = players.findIndex((player) => Number(player.id) === Number(this.gameui.player_id));
      return playerIndex > 0 ? [...players.slice(playerIndex), ...players.slice(0, playerIndex)] : players;
   }

   // public getPlayerTable(playerId: number): PlayerTable {
   //    // const playerTable = this.playersTables.find((table) => table.player_id === playerId);
   //    if (!playerTable) {
   //       throw new Error(`Player table not found for player ID: ${playerId}`);
   //    }
   //    return playerTable;
   // }

   public setupNotifications() {
      this.notificationManager = new NotificationManager(this);
      this.notificationManager.setup();
   }

}

export { Game };
