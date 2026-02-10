import { BgaCards } from "../libs";
import { Card } from "../types/game";
import { Game } from "../game";

export class PlayerHand extends BgaCards.HandStock<Card> {
   constructor(game: Game) {
      super(game.cardManager, document.querySelector(".swd-player-hand")!, {
         cardOverlap: 50,
         emptyHandMessage: _("You have no cards in your hand"),
         floatZIndex: 5,
      });
   }
}