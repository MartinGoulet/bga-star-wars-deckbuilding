const isDebug = window.location.host == "studio.boardgamearena.com" || window.location.hash.indexOf("debug") > -1;

export const debugLog = (...args: any[]) => {
   if (isDebug) console.log(...args);
};

export function createCounter(id: string | HTMLElement, value: number = 0): Counter {
   const counter = new ebg.counter();
   counter.create(id);
   counter.setValue(value);
   return counter;
}
