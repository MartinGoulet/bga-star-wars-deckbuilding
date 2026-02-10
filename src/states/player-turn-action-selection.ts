import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnActionSelectionArgs {
   selectableCardIds: number[];
   selectableGalaxyCardIds: number[];
   selectableAbilityCardIds: number[];
   canCommitAttack: boolean;
}

export class PlayerTurnActionSelectionState extends BaseState<PlayerTurnActionSelectionArgs> {
   onEnteringState(args: PlayerTurnActionSelectionArgs, isCurrentPlayerActive: boolean): void {
      if (!isCurrentPlayerActive) return;

      if (args.canCommitAttack) {
         const handle = async () => await this.game.actions.performAction("actCommitAttack");
         this.game.statusBar.addActionButton(_("Commit to an attack"), handle);
      }

      const handleEndTurn = async () => await this.game.actions.performAction("actEndTurn");
      this.game.statusBar.addActionButton(_("End Turn"), handleEndTurn, {
         color: "alert",
         confirm: () => {
            if (args.selectableCardIds.length > 0) {
               return _("You have playable cards in your hand. Are you sure you want to end your turn?");
            } else if (args.selectableGalaxyCardIds.length > 0) {
               return _(
                  "You have purchasable cards in the Galaxy Row and resources available. Are you sure you want to end your turn?",
               );
            } else if (args.canCommitAttack) {
               return _("You can still commit to an attack. Are you sure you want to end your turn?");
            }
            return null;
         },
      });

      this.setupPlayerHandSelectableCards(args);
      this.setupGalaxyRowSelectableCards(args);
      this.setupPlayerPlayAreaSelectableCards(args);
      this.setupOuterRimSelectableCards(args);
   }

   private setupPlayerHandSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const selectableCards = this.game.playerHand
         .getCards()
         .filter((card) => args.selectableCardIds.includes(card.id));

      this.game.playerHand.setSelectionMode("single");
      this.game.playerHand.setSelectableCards(selectableCards);
      this.game.playerHand.onCardClick = async (card: Card) => {
         if (!args.selectableCardIds.includes(card.id)) return;
         this.game.playerHand.unselectAll(true);
         if ((this.game.gameui as any).isInterfaceLocked()) return;
         await this.game.actions.performAction("actPlayCard", { cardId: card.id });
      };
   }

   private setupGalaxyRowSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const galaxyRow = this.game.tableCenter.galaxyRow;

      const selectableCards = galaxyRow.getCards().filter((card) => args.selectableGalaxyCardIds.includes(card.id));

      galaxyRow.setSelectionMode("single");
      galaxyRow.setSelectableCards(selectableCards);
      galaxyRow.onCardClick = async (card: Card) => {
         galaxyRow.unselectCard(card, true);
         await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
      };
   }

   private setupOuterRimSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const outerRimDeck = this.game.tableCenter.outerRimDeck;

      const selectableCards = outerRimDeck.getCards().filter((card) => args.selectableGalaxyCardIds.includes(card.id));

      outerRimDeck.setSelectionMode("single");
      outerRimDeck.setSelectableCards(selectableCards);
      outerRimDeck.onCardClick = async (card: Card) => {
         outerRimDeck.unselectCard(card, true);
         await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
      };
   }

   private setupPlayerPlayAreaSelectableCards(args: PlayerTurnActionSelectionArgs): void {
      const { playArea, ships, activeBase } = this.game.getCurrentPlayerTable();

      [playArea, ships, activeBase].forEach((area) => {
         const selectableCards = area.getCards().filter((card) => args.selectableAbilityCardIds.includes(card.id));

         if(selectableCards.length === 0) return;

         area.setSelectionMode("single");
         area.setSelectableCards(selectableCards);
         area.onCardClick = async (card: Card) => {
            area.unselectCard(card, true);
            await this.game.actions.performAction("actUseCardAbility", { cardId: card.id });
         };
      });
   }
}
