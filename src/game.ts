import { MyCardManager } from "./card-manager/card-manager";
import { BgaAnimations } from "./libs";
import { NotificationManager } from "./notification-manager";
import { PlayerHand } from "./player-hand/player-hand";
import { PlayerTable } from "./player-table/player-table";
import { StateManager } from "./state-manager";
import { TableCenter } from "./table-center/table-center";
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
   // @ts-ignore
   public tableCenter: TableCenter;
   // @ts-ignore
   public playerHand: PlayerHand;
   // @ts-ignore
   public cardManager: MyCardManager;

   public animationManager: InstanceType<typeof BgaAnimations.Manager>;
   public stateManager: StateManager;
   private playerTables: PlayerTable[] = []; 


   constructor(bga: Bga<StarWarsGamedatas>) {
      Object.assign(this, bga);
      Object.assign(this.gameui, {game: this});

      this.gameArea.getElement().insertAdjacentHTML(
         "beforeend",
         `<div class="swd-table-wrapper">
            <div class="swd-player-table-opponent"></div>
            <div class="swd-table-center"></div>
            <div class="swd-player-table-current"></div>
            <div class="swd-player-hand"></div>
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

      const orderedPlayers = this.getOrderedPlayers();
      this.cardManager = new MyCardManager(this, orderedPlayers[0]!);

      this.tableCenter = new TableCenter(this);
      this.playerTables = this.createPlayerTables();
      this.setupPlayerHand();

      this.setupNotifications();
   }

   public getOrderedPlayers(): StarWarsPlayer[] {
      const players = Object.values(this.gamedatas.players).sort((a, b) => a.playerNo - b.playerNo);
      const playerIndex = players.findIndex((player) => Number(player.id) === Number(this.gameui.player_id));
      return playerIndex > 0 ? [...players.slice(playerIndex), ...players.slice(0, playerIndex)] : players;
   }

   public getPlayerTable(playerId: number): PlayerTable {
      const playerTable = this.playerTables.find((table) => table.playerId === playerId);
      if (!playerTable) {
         throw new Error(`Player table not found for player ID: ${playerId}`);
      }
      return playerTable;
   }

   public setupNotifications() {
      this.notificationManager = new NotificationManager(this);
      this.notificationManager.setup();
   }

   private createPlayerTables(): PlayerTable[] {
      const orderedPlayers = this.getOrderedPlayers();
      return orderedPlayers.map((player, index) => {
         const isCurrentPlayer = index === 0;
         return new PlayerTable(this, player, isCurrentPlayer);
      });
   }



   private setupPlayerHand(): void {
      if (this.players.isCurrentPlayerSpectator()) return;
      this.playerHand = new PlayerHand(this);
      if( this.gamedatas.playerHand ) {
         this.playerHand.addCards(this.gamedatas.playerHand);
      }
   }

}

export { Game };
