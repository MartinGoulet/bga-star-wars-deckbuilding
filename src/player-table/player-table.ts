import { Game } from "../game";
import { BgaCards } from "../libs";
import { Card, StarWarsPlayer } from "../types/game";
// import { createCounter } from "../utils";

export class PlayerTable {
   public readonly playerId: number;
   // @ts-ignore
   public playArea: InstanceType<typeof BgaCards.LineStock<Card>>;
   // @ts-ignore
   public resourceCounter: ebg.counter;
   // @ts-ignore
   public discard: InstanceType<typeof BgaCards.Deck<Card>>;
   // @ts-ignore
   public deck: InstanceType<typeof BgaCards.Deck<Card>>;
   // @ts-ignore
   public activeBase: InstanceType<typeof BgaCards.LineStock<Card>>;
   // @ts-ignore
   public ships: InstanceType<typeof BgaCards.LineStock<Card>>;

   constructor(private game: Game, public player: StarWarsPlayer, public isCurrentPlayer: boolean) {
      this.playerId = Number(player.id);

      const color: string = `#${player.color}`; // player.faction === "Rebel" ? "#2a9d8f" : "#ff2a2b";

      const html = `<div id="player-table-${this.playerId}" class="swd-player-table" data-player-id="${this.playerId}" style="--color-border: ${color};">
         <div class="swd-player-info">
            <div class="swd-player-name">${player.name}</div>
            <div class="swd-player-resources">Resources: <span id="player-resources-${this.playerId}"></span></div>
         </div>
         <div class="swd-player-area">
            <div class="swd-play-area"></div>
            <div class="swd-player-decks">
               <div class="swd-player-base-section">
                  <div class="swd-player-ships"></div>
                  <div class="swd-player-active-base"></div>
               </div>
               <div class="swd-player-deck"></div>
               <div class="swd-player-discard"></div>
            </div>
         </div>
      </div>`;

      const containerSelector = isCurrentPlayer ? ".swd-player-table-current" : ".swd-player-table-opponent";
      const container = document.querySelector(containerSelector);
      if (container) {
         container.insertAdjacentHTML("beforeend", html);
      }

      this.setupResourceCounter(player);
      this.setupPlayArea(player);
      this.setupDeckAndDiscard(player);
      this.setupBaseAndShips(player);
   }

   private setupBaseAndShips(player: StarWarsPlayer): void {
      this.activeBase = new BgaCards.LineStock<Card>(
         this.game.cardManager,
         document.querySelector(`#player-table-${this.playerId} .swd-player-active-base`)!,
         {
            center: true,
         }
      );
      this.ships = new BgaCards.LineStock<Card>(
         this.game.cardManager,
         document.querySelector(`#player-table-${this.playerId} .swd-player-ships`)!,
         {
            center: true,
         }
      );

      if (player.ships) this.ships.addCards(player.ships);

      if (player.activeBase) this.activeBase.addCard(player.activeBase);
   }

   private setupDeckAndDiscard(player: StarWarsPlayer): void {
      this.deck = new BgaCards.Deck<Card>(
         this.game.cardManager,
         document.querySelector(`#player-table-${this.playerId} .swd-player-deck`)!,
         {
            cardNumber: player.deckCount,
            counter: {
               show: true,
               size: 6,
               position: "bottom-right",
            },
            fakeCardGenerator: (deckId: string) => {
               return { id: this.playerId } as Card;
            },
         }
      );
      this.discard = new BgaCards.Deck<Card>(
         this.game.cardManager,
         document.querySelector(`#player-table-${this.playerId} .swd-player-discard`)!,
         {
            autoRemovePreviousCards: false,
            counter: {
               show: true,
               size: 6,
               position: "bottom-right",
            },
         }
      );

      this.discard.addCards(player.discard);
   }

   private setupResourceCounter(player: StarWarsPlayer): void {
      this.resourceCounter = new ebg.counter();
      this.resourceCounter.create(document.getElementById(`player-resources-${this.playerId}`)!, {
         value: player.resources,
         playerCounter: "resources",
         playerId: this.playerId,
      });
   }

   private setupPlayArea(player: StarWarsPlayer): void {
      this.playArea = new BgaCards.LineStock<Card>(
         this.game.cardManager,
         document.querySelector(`#player-table-${this.playerId} .swd-play-area`)!,
         {
            center: false,
            selectedCardStyle: {
               outlineColor: "#00FFFF",
            },
         }
      );

      this.playArea.addCards(player.playAreaCards);
   }
}
