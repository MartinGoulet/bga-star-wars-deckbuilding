import { Card } from "../types/game";
import { BaseState } from "./base-state";

interface SoloEnemyChooseTargetArgs {
   targets: Card[];
   reason?: string;
}

export class SoloEnemyChooseTargetState extends BaseState<SoloEnemyChooseTargetArgs> {
   onEnteringState(args: SoloEnemyChooseTargetArgs, isCurrentPlayerActive: boolean): void {
      const stocks = this.game.playerTables.flatMap((table) => [table.activeBase, table.ships]);
      stocks.push(this.game.tableCenter.galaxyRow);
      if (this.game.soloEnemyBoard) {
         stocks.push(...this.game.soloEnemyBoard.getAttackTargetStocks());
      }

      stocks.forEach((stock) => {
         stock.setSelectionMode("single");
         stock.setSelectableCards(args.targets);
         stock.onCardClick = async (card: Card) => {
            stock.unselectCard(card, true);
            if (isCurrentPlayerActive && args.targets.some((target) => target.id === card.id)) {
               await this.game.actions.performAction("actChooseTarget", { cardId: card.id });
            }
         };
      });
   }
}