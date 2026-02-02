import { Game } from "../game";
import { Card } from "../types/game";
import { BgaCards } from "../libs";
import { DiscardWithPopup } from "../stocks/discard-with-popup";

export class TableCenter {
   public galaxyRow: InstanceType<typeof BgaCards.LineStock<Card>>;
   public galaxyDeck: InstanceType<typeof BgaCards.Deck<Card>>;
   public galaxyDiscard: DiscardWithPopup;
   public outerRimDeck: InstanceType<typeof BgaCards.Deck<Card>>;

   constructor(private game: Game) {
      this.game.gameArea
         .getElement()
         .querySelector(".swd-table-center")!
         .insertAdjacentHTML(
            "beforeend",
            `<div>
               <div class="galaxy-row-label">Galaxy Row</div>
               <div class="galaxy-row-wrapper">
                  <div>
                     <div class="galaxy-decks">
                        <div class="deck-draw-pile"></div>
                        <div class="deck-discard-pile"></div>
                        <div class="force-track">
                           <div class="force-track-background"></div>
                           <div class="force-track-indicator" data-force="${game.gamedatas.force}"></div>
                        </div>
                        <div class="deck-outer-rim"></div>
                     </div>
                  </div>
                  <div>
                     <div class="galaxy-row" id="galaxy-row"></div>
                  </div>
               </div>
            </div>`
         );

      this.galaxyRow = new BgaCards.LineStock<Card>(game.cardManager, document.getElementById("galaxy-row")!, {
         gap: '12px',
      });
      this.galaxyDeck = new BgaCards.Deck<Card>(game.cardManager, document.querySelector(".deck-draw-pile")!, {
         cardNumber: game.gamedatas.galaxyDeckCount,
         counter: {
            show: true,
            size: 6,
            position: 'bottom-right',
         }
      });
      this.galaxyDiscard = new DiscardWithPopup(game, game.cardManager, document.querySelector(".deck-discard-pile")!, {
         autoRemovePreviousCards: false,
         counter: {
            show: true,
            size: 6,
            position: 'bottom-right',
         }
      });

      this.outerRimDeck = new BgaCards.Deck<Card>(game.cardManager, document.querySelector(".deck-outer-rim")!, {
         autoRemovePreviousCards: false,
         fakeCardGenerator: (deckId) => this.outerRimDeck.getCards().pop()!,
         counter: {
            show: true,
            size: 6,
            position: 'bottom-right',
         }
      });

      this.galaxyRow.addCards(game.gamedatas.galaxyRow);
      this.galaxyDiscard.addCards(game.gamedatas.galaxyDiscard);
      this.outerRimDeck.addCards(game.gamedatas.outerRimDeck);
   }

   public onLeaveState(): void {
      [this.galaxyRow, this.galaxyDeck, this.galaxyDiscard].forEach((stock) => {
         stock.setSelectionMode("none");
         stock.onCardClick = undefined;
         stock.onSelectionChange = undefined;
      });
   }

   public setForceCounter(value: number): void {
      const indicator = this.game.gameArea
         .getElement()
         .querySelector(".force-track-indicator") as HTMLDivElement;
      if (!indicator) return;
      indicator.setAttribute("data-force", value.toString());
   }
}
