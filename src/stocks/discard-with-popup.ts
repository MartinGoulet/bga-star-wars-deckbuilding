import { Game } from "../game";
import { BgaCards } from "../libs";
import { DeckSettings, RemoveCardFromDeckSettings } from "../types/bga-cards";
import { Card } from "../types/game";

export class DiscardWithPopup extends BgaCards.Deck<Card> {
   private lineDiscard: InstanceType<typeof BgaCards.LineStock<Card>> | null = null;

   constructor(
      private game: Game,
      protected cardManager: InstanceType<typeof BgaCards.Manager<Card>>,
      node: HTMLElement,
      options: DeckSettings<Card>,
   ) {
      super(cardManager, node, {
         ...options,
         autoUpdateCardNumber: false,
         fakeCardGenerator: (deckId: string) => {
            const cards = this.getCards();
            return cards[cards.length - 1] || ({ id: deckId } as any);
         },
      });
      node.classList.add("discard-with-popup");
      node.insertAdjacentHTML("afterbegin", `<div class="swd-special-discard">${_("View Discard")}</div>`);
      const el = node.querySelector(".swd-special-discard") as HTMLElement;
      el.onclick = this.displayDiscardOverlay.bind(this);
   }

   public addCard(card: Card, settings?: any): Promise<boolean> {
      const addPromise = super.addCard(card, settings);
      addPromise.then((added) => {
         this.setCardNumber(this.getCards().length);
      });
      return addPromise;
   }

   public removeCard(card: Card, settings?: any): Promise<boolean> {
      const removePromise = super.removeCard(card, settings);
      removePromise.then((removed) => {
         this.setCardNumber(this.getCards().length);
      });
      return removePromise;
   }

   private async displayDiscardOverlay(): Promise<void> {
      const html = `
         <div class="swd-discard-overlay visible">
            <div class="swd-discard-overlay-content">
               <div class="swd-discard-overlay-header">
                  <span class="swd-discard-overlay-title">${_("Discard Pile")}</span>
                  <span class="swd-discard-overlay-close">&times;</span>
               </div>
               <div class="swd-discard-overlay-body">
                  <div class="swd-discard-overlay-cards"></div>
               </div>
            </div>
         </div>`;

      document.querySelector(".swd-table-center")!.insertAdjacentHTML("afterbegin", html);
      const closeBtn = document.querySelector(".swd-discard-overlay-close") as HTMLElement;
      closeBtn.onclick = this.closePopup.bind(this);

      this.lineDiscard = new BgaCards.LineStock<Card>(
         this.game.discardCardManager,
         document.querySelector(".swd-discard-overlay-cards") as HTMLElement,
         {
            center: true,
            gap: "15px",
            selectedCardStyle: {
               outlineColor: "#00FFFF",
            },
         },
      );

      const cards = this.getCards().map((card) => ({ ...card })); // create shallow copies to avoid issues with references
      await this.lineDiscard.addCards(cards, {}, false);

      this.lineDiscard.setSelectionMode(this.selectionMode);
      this.lineDiscard.setSelectableCards(this.selectableCards);
      this.lineDiscard.onCardClick = (card: Card) => {
         this.onCardClick?.(card);
      };
      this.lineDiscard.onSelectionChange = (selection: Card[], lastChange: Card | null) => {
         this.onSelectionChange?.(selection, lastChange);
      };

      super.getSelection().forEach((card) => this.lineDiscard?.selectCard(card, true));
   }

   // public selectAll(silent?: boolean): void {
   //    super.selectAll(silent);
   //    this.lineDiscard?.selectAll(silent);
   // }

   // public unselectAll(silent?: boolean): void {
   //    super.unselectAll(silent);
   //    this.lineDiscard?.unselectAll(silent);
   // }

   // public selectCard(card: Card, silent?: boolean): void {
   //    super.selectCard(card, silent);
   //    this.lineDiscard?.selectCard(card, silent);
   // }

   // public unselectCard(card: Card, silent?: boolean): void {
   //    super.unselectCard(card, silent);
   //    this.lineDiscard?.unselectCard(card, silent);
   // }

   public closePopup(): void {
      const selection = this.lineDiscard?.getSelection();
      if (this.lineDiscard) {
         this.lineDiscard.onCardClick = undefined;
         this.lineDiscard.onSelectionChange = undefined;
         this.lineDiscard.removeAll();
         this.game.discardCardManager.removeStock(this.lineDiscard);
         this.lineDiscard = null;
      }

      const overlay = document.querySelector(".swd-discard-overlay") as HTMLElement;
      overlay?.remove();

      super.unselectAll(true);
      selection?.forEach((card) => super.selectCard(card, true));
   }

   public getSelection(): Card[] {
      return this.lineDiscard ? this.lineDiscard.getSelection() : super.getSelection();
   }
}
