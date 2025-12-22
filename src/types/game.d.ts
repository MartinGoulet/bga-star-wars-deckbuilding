export interface StarWarsPlayer extends Player {
   playerNo: number;
}

export interface StarWarsGamedatas extends Gamedatas<StarWarsPlayer> {
}

export interface Card {
   id: string;
   type: string;
   type_arg: string;
   location: string;
   location_arg: string;
}

export interface StateHandler<T> {
   onEnteringState(args: T, isCurrentPlayerActive: boolean): void;
   onLeavingState(isCurrentPlayerActive: boolean): void;
   onUpdateActionButtons?(args: T, isCurrentPlayerActive: boolean): void;
}
