export interface StarWarsPlayer extends Player {
   playerNo: number;
   faction: string;
   resources: number;
   playAreaCards: Card[];
   deckCount: number;
   discard: Card[];
   ships?: Card[];
   activeBase?: Card;
}

export interface StarWarsGamedatas extends Gamedatas<StarWarsPlayer> {
   card_types: { [key: number]: CardType };
   galaxyRow: Card[];
   galaxyDiscard: Card[];
   galaxyDeckCount: number;
   playerHand?: Card[];
   force: number;
}

export interface Card {
   id: number;
   type_arg: string;
   location: string;
   location_arg: string;
   name: string;
   faction: string;
   type: string;
   img: string;
}

export interface StateHandler<T> {
   onEnteringState(args: T, isCurrentPlayerActive: boolean): void;
   onLeavingState(isCurrentPlayerActive: boolean): void;
   onUpdateActionButtons?(args: T, isCurrentPlayerActive: boolean): void;
}
