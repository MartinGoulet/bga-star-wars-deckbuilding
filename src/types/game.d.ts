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
   outerRimDeck: Card[];
   force: number;
}

export interface Card {
   id: number;
   type: string;
   typeArg: number;
   location: string;
   locationArg: number;
   name: string;
   gametext?: string
   faction: string;
   type: string;
   img: string;
   damage: number;
   health: number;
}


export interface StateHandler<T> {
   /**
    * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
    */
   onEnteringState(args: T, isCurrentPlayerActive: boolean): void;
   /**
    * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
    */
   onLeavingState(isCurrentPlayerActive: boolean): void;
}

export interface MultipleActiveStateHandler<T> extends StateHandler<T> {
   /**
    * This method is called each time the current player becomes active or inactive in a MULTIPLE_ACTIVE_PLAYER state. You can use this method to perform some user interface changes at this moment.
    * on MULTIPLE_ACTIVE_PLAYER states, you may want to call this function in onEnteringState using `this.onPlayerActivationChange(args, isCurrentPlayerActive)` at the end of onEnteringState.
    * If your state is not a MULTIPLE_ACTIVE_PLAYER one, you can delete this function.
    */
   onPlayerActivationChange(args: T, isCurrentPlayerActive: boolean): void;
}
