import type { BgaAnimations as BgaAnimationsType } from "./types/bga-animations";
import type { BgaCards as BgaCardsType } from "./types/bga-cards";

const BgaAnimations: typeof BgaAnimationsType = await (globalThis as any).importEsmLib("bga-animations", "1.x");
const BgaCards: typeof BgaCardsType = await (globalThis as any).importEsmLib("bga-cards", "1.x");
const [tippy] = await (globalThis as any).importDojoLibs([g_gamethemeurl + 'modules/js/vendor/tippy-headless.min.js']);

export { BgaAnimations, BgaCards, tippy };