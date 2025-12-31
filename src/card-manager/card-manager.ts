import { Game } from "../game";
import { BgaCards } from "../libs";
import { Card, StarWarsPlayer } from "../types/game";

export class MyCardManager extends BgaCards.Manager<Card> {
   constructor(private game: Game, private currentPlayer: StarWarsPlayer) {
      super({
         animationManager: game.animationManager,
         type: "card",
         getId: (card: Card) => card.id.toString(),
         setupDiv: (card: Card, cardDiv: HTMLElement) => {
            if ("type" in card) cardDiv.dataset.type = card.type.toLowerCase();
            if ("faction" in card) {
                cardDiv.dataset.faction = card.faction;
                cardDiv.dataset.isNeutral = card.faction === "Neutral" ? "true" : "false";
                cardDiv.dataset.isAlly = card.faction !== "Neutral" && card.faction === this.currentPlayer.faction ? "true" : "false";
                cardDiv.dataset.isEnemy = card.faction !== "Neutral" && card.faction !== this.currentPlayer.faction ? "true" : "false";
            }
         },
         setupFrontDiv: (card: Card, frontDiv: HTMLElement) => {
            frontDiv.dataset.img = card.img;
         },
         isCardVisible: (card: Card) => "img" in card,
         cardBorderRadius: "8px",
         cardWidth: 120,
         cardHeight: 168,
      });
   }

   public setCardAsSelected(card: Card): void {
      this.getCardElement(card)?.classList.add("bga-cards_selected-card");
   }

   public removeAllCardsAsSelected(): void {
      document.querySelectorAll(".bga-cards_selected-card").forEach((cardElement) => {
         cardElement.classList.remove("bga-cards_selected-card");
      });
   }
}
