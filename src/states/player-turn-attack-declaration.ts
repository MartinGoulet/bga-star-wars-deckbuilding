import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface PlayerTurnAttackDeclarationArgs {
   targets: Card[];
}

export class PlayerTurnAttackDeclarationState extends BaseState<PlayerTurnAttackDeclarationArgs> {
   onEnteringState(args: PlayerTurnAttackDeclarationArgs, isCurrentPlayerActive: boolean): void {
      const stocks = this.game.playerTables.flatMap((table) => [table.activeBase, table.ships]);
      stocks.push(this.game.tableCenter.galaxyRow);

      stocks.forEach((stock) => {
         stock.setSelectionMode("single");
         stock.setSelectableCards(args.targets);
         stock.onCardClick = async (card: Card) => {
            stock.unselectCard(card, true);
            if (isCurrentPlayerActive && args.targets.find((c) => c.id === card.id)) {
               await this.game.actions.performAction("actDeclareAttack", { cardId: card.id });
            }
         };
      });

      const handleCancel = async () => this.game.actions.performAction("actCancel");
      this.game.statusBar.addActionButton(_('Cancel'), handleCancel, {
         color: 'alert'
      });
   }
}
