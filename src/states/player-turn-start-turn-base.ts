import { Game } from "../game";
import { BgaCards } from "../libs";
import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnStartTurnBaseArgs {
   selectableBases: Card[];
}

export class PlayerTurnStartTurnBaseState extends BaseState<PlayerTurnStartTurnBaseArgs> {
   private bases: InstanceType<typeof BgaCards.LineStock<Card>>;

   constructor(game: Game) {
      super(game);
      this.bases = new BgaCards.LineStock<Card>(
         this.game.cardManager,
         document.querySelector(".swd-base-selection")!,
         {
            gap: '20px',
         },
      );
   }
   public onEnteringState(args: PlayerTurnStartTurnBaseArgs, isCurrentPlayerActive: boolean): void {
      this.bases.addCards(args.selectableBases, { animationsActive: false });
      if (!isCurrentPlayerActive) return;

      this.bases.setSelectionMode("single");
      this.bases.setSelectableCards(args.selectableBases);
      this.bases.onCardClick = (card: Card) => {
         this.game.actions.performAction("actSelectBase", { cardId: card.id });
      };
   }

   public onLeavingState(isCurrentPlayerActive: boolean): void {
      this.bases.removeAll();
      this.bases.onCardClick = undefined;
      super.onLeavingState(isCurrentPlayerActive);
   }
}
