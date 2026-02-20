import { DiscardCardManager, MyCardManager } from "./card-manager/card-manager";
import { BgaAnimations } from "./libs";
import { NotificationManager } from "./notification-manager";
import { PlayerHand } from "./player-hand/player-hand";
import { PlayerTable } from "./player-table/player-table";
import { EffectCardSelectionState } from "./states/effect-card-selection";
import { PlayerTurnActionResolveDamageShipBaseState } from "./states/player-turn-action-resolve-damage-ship-base";
import { PlayerTurnActionSelectionState } from "./states/player-turn-action-selection";
import { PlayerTurnAskChoiceState } from "./states/player-turn-ask-choice";
import { PlayerTurnAttackCommitState } from "./states/player-turn-attack-commit";
import { PlayerTurnAttackDeclarationState } from "./states/player-turn-attack-declaration";
import { PlayerTurnStartTurnBaseState } from "./states/player-turn-start-turn-base";
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
   // @ts-ignore
   public discardCardManager: DiscardCardManager;

   public animationManager: InstanceType<typeof BgaAnimations.Manager>;
   public playerTables: PlayerTable[] = [];

   constructor(bga: Bga<StarWarsGamedatas>) {
      Object.assign(this, bga);
      Object.assign(this.gameui, { game: this });

      this.gameArea.getElement().insertAdjacentHTML(
         "beforeend",
         `<div class="swd-table-wrapper" id="swd-table-wrapper">
            <div class="swd-base-selection"></div>
            <div class="swd-player-table-opponent"></div>
            <div class="swd-table-center"></div>
            <div class="swd-player-table-current"></div>
            <div class="swd-player-hand"></div>
          </div>`,
      );

      this.animationManager = new BgaAnimations.Manager({
         animationsActive: () => this.gameui.bgaAnimationsActive(),
      });

   }

   public setup(gamedatas: StarWarsGamedatas) {
      this.gamedatas = gamedatas;

      const orderedPlayers = this.getOrderedPlayers();
      this.cardManager = new MyCardManager(this, orderedPlayers[0]!);
      this.discardCardManager = new DiscardCardManager(this);

      this.tableCenter = new TableCenter(this);
      this.playerTables = this.createPlayerTables();
      this.setupPlayerHand();

      this.registerStates();

      this.setupNotifications();
   }

   private registerStates() {
      this.states.logger = console.log;
      this.states.register("playerTurnActionSelection", new PlayerTurnActionSelectionState(this));
      this.states.register("playerTurnAskChoice", new PlayerTurnAskChoiceState(this));
      this.states.register("playerTurnAttackDeclaration", new PlayerTurnAttackDeclarationState(this));
      this.states.register("playerTurnAttackCommit", new PlayerTurnAttackCommitState(this));
      this.states.register("effectCardSelection", new EffectCardSelectionState(this));
      this.states.register("playerTurnStartTurnBase", new PlayerTurnStartTurnBaseState(this));
      this.states.register("PlayerTurn_ActionResolveDamageShipBase", new PlayerTurnActionResolveDamageShipBaseState(this));
   }

   public getCurrentPlayerTable(): PlayerTable {
      return this.getPlayerTable(this.players.getCurrentPlayerId());
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
         return new PlayerTable(this, player, isCurrentPlayer, index);
      });
   }

   private setupPlayerHand(): void {
      if (this.players.isCurrentPlayerSpectator()) return;
      this.playerHand = new PlayerHand(this);
      if (this.gamedatas.playerHand) {
         this.playerHand.addCards(this.gamedatas.playerHand);
      }
   }

   public closeDiscardPopupIfNeeded(): void {
      this.playerTables.forEach((playerTable) => {
         playerTable.discard.closePopup();
      });
   }

   bgaFormatText(log: string, args: any): { log: string; args: any } {
      try {
         if (log && args && !args.processed) {
            args.processed = true;

            ["card_name"].forEach((field) => {
               if (args[field] !== null && args[field] !== undefined) {
                  args[field] = `<strong>${_(args[field])}</strong>`;
               }
            });

            ["card_names"].forEach((field) => {
               if (args[field] !== null && args[field] !== undefined && Array.isArray(args[field])) {
                  args[field] = args[field].map((name: string) => `<strong>${_(name)}</strong>`).join(", ");
               }
            });

            // if (args["_private"]) {
            //    const logs = this.bgaFormatText(log, args["_private"]);
            //    log = logs.log;
            //    args["_private"] = logs.args;
            // }
         }
      } catch (e: any) {
         console.error(log, args, "Exception thrown", e.stack);
      }

      return { log, args };
   }
}

export { Game };
