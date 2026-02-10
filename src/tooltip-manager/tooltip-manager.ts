import { Game } from "../game";
import { tippy } from "../libs";
import { Card } from "../types/game";

declare function bga_format(str: string, formatters: { [key: string]: (text: string) => string | string }): string;

export class TooltipManager {
   private tooltips: Record<string, any> = {};

   constructor(private game: Game) {}

   public addTooltip(
      element: HTMLElement,
      card: Card,
      placement: 'auto' | 'left' | 'right' | 'top' | 'bottom' = 'auto',
   ) {
      if (this.game.userPreferences.get(200) !== 0) return;
      if (this.tooltips[element.id] !== undefined) {
         this.tooltips[element.id].destroy();
      }

      const content = this.getTooltip(element.id, card);

      this.tooltips[element.id] = tippy(element, {
         content,
         placement,
         touch: ['hold', 400],
         delay: [500, 100],
         appendTo: document.getElementById('swd-table-wrapper')!,
         render(instance: any) {
            // The recommended structure is to use the popper as an outer wrapper
            // element, with an inner `box` element
            const popper = document.createElement('div') as HTMLDivElement;
            const arrow = document.createElement('arrow') as HTMLElement;
            arrow.dataset.popperArrow = 'true';
            arrow.classList.add('tooltip-arrow');
            popper.appendChild(arrow);

            const box = document.createElement('div');
            popper.appendChild(box);

            popper.className = 'swd-tooltip';
            box.insertAdjacentHTML('beforeend', instance.props.content);

            function onUpdate(prevProps: any, nextProps: any) {
               // DOM diffing
               if (prevProps.content !== nextProps.content) {
                  box.innerHTML = nextProps.content;
               }
            }

            return {
               popper,
               onUpdate,
            };
         },
      });
   }

   private getTooltip(id: string, card: Card) {
      return `<div id="${id}-tooltip" class="swd-card-tooltip">
         <div class="card-tooltip-frame">${this.getTooltipCard(card)}</div>
         <div class="tooltip-explanation">${this.getExplanation(card)}</div>
      </div>`;
   }
   private getTooltipCard(card: Card) {
      if (!("type" in card)) debugger;
      return `
         <div class="bga-cards_card card" data-type="${card.type.toLowerCase()}" data-faction="${card.faction}">
            <div class="bga-cards_card-sides">
               <div class="bga-cards_card-side front" data-img="${card.img}"></div>
            </div>
         </div>`;
   }

   private getExplanation(card: Card) {

      // const cardInfo = this.game.characterManager.getCardType(card);
      // const gametext = this.game.replaceSpecialTag(_(cardInfo.gametext));
      // let gametext2 = cardInfo.gametext2 ? this.game.replaceSpecialTag(_(cardInfo.gametext2)) : '';

      // if (gametext2) {
      //    gametext2 = `<div class="explanation">
      //       <div class="explanation-gametext">${_(gametext2)}</div>
      //    </div>`;
      // }

      let gametext = '';
      if (card.gametext) {
         gametext = bga_format(_(card.gametext), {
            '*': (t) => '<b>' + t + '</b>',
         });
         gametext = `<div class="explanation">
            <div class="explanation-gametext">${gametext}</div>
         </div>`;
      }

      return `<div class="explanation">
         <div class="explanation-title">${_(card.name)}</div>
      </div>
      ${gametext}`;
   }
}
