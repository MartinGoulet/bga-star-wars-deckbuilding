import { Game } from "../game";
import { BgaAnimations, BgaCards } from "../libs";
import { TooltipManager } from "../tooltip-manager/tooltip-manager";
import { Card, StarWarsPlayer } from "../types/game";

export class MyCardManager extends BgaCards.Manager<Card> {
   private readonly tooltipManager: TooltipManager;
   constructor(private game: Game, private currentPlayer: StarWarsPlayer, private type: string = "card") {
      super({
         animationManager: game.animationManager,
         type: type,
         getId: (card: Card) => card.id.toString(),
         setupDiv: (card: Card, cardDiv: HTMLElement) => {
            if ("type" in card) cardDiv.dataset.type = card.type.toLowerCase();
            if ("faction" in card) {
               cardDiv.dataset.faction = card.faction;
               cardDiv.dataset.isNeutral = card.faction === "Neutral" ? "true" : "false";
               cardDiv.dataset.isAlly =
                  card.faction !== "Neutral" && card.faction === this.currentPlayer.faction ? "true" : "false";
               cardDiv.dataset.isEnemy =
                  card.faction !== "Neutral" && card.faction !== this.currentPlayer.faction ? "true" : "false";
            }
         },
         setupFrontDiv: (card: Card, frontDiv: HTMLElement) => {
            frontDiv.dataset.img = card.img;
            if("type" in card) {
               this.tooltipManager.addTooltip(frontDiv, card);
            }
            if ("damage" in card && card.damage > 0) {
               this.setDamageOnCard(card);
            }
         },
         isCardVisible: (card: Card) => "img" in card,
         cardBorderRadius: "8px",
         cardWidth: 120,
         cardHeight: 168,
      });
      this.tooltipManager = new TooltipManager(this.game);
   }

   public setCardAsSelected(card: Card): void {
      if (!card) {
         console.warn("setCardAsSelected called with null card");
         return;
      };
      this.getCardElement(card)?.classList.add("bga-cards_selected-card");
   }

   public removeAllCardsAsSelected(): void {
      document.querySelectorAll(".bga-cards_selected-card").forEach((cardElement) => {
         cardElement.classList.remove("bga-cards_selected-card");
      });
   }

   public setDamageOnCard(card: Card): void {
      const cardElement = this.getCardElement(card);
      if (!cardElement) return;

      let damageDiv = cardElement.querySelector(".card-damage") as HTMLElement;
      if (!damageDiv) {
         damageDiv = document.createElement("div");
         damageDiv.classList.add("card-damage");
         cardElement.querySelector(".bga-cards_card-sides")!.appendChild(damageDiv);
      }
      damageDiv.innerText = (card.health - card.damage).toString();
   }
}

export class DiscardCardManager extends BgaCards.Manager<Card> {
   constructor(private game: Game) {
      super({
         animationManager: new BgaAnimations.Manager({
            animationsActive: () => false,
         }),
         type: "discard",
         getId: (card: Card) => card.id.toString(),
         setupDiv: (card: Card, cardDiv: HTMLElement) => {
            if ("type" in card) cardDiv.dataset.type = card.type.toLowerCase();
            if ("faction" in card) {
               cardDiv.dataset.faction = card.faction;
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
}
