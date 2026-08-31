import { Game } from "../game";
import { BgaCards } from "../libs";
import { Card, SoloEnemyData } from "../types/game";

const SOLO_ZONES = {
    leader: "leader",
    activeBase: "activeBase",
    shuttles: "shuttles",
    muster: "muster",
    musterHidden: "musterHidden",
    play: "play",
    reserve: "reserve",
} as const;

type SoloZone = typeof SOLO_ZONES[keyof typeof SOLO_ZONES];

export class SoloEnemyBoard {
    private readonly stocks: Record<SoloZone, InstanceType<typeof BgaCards.LineStock<Card>>>;
    private readonly resourcesElement: HTMLElement;
    private readonly progressElement: HTMLElement;
    private readonly progressTrackElement: HTMLElement;

    constructor(private game: Game, data: SoloEnemyData) {
        const container = document.querySelector(".swd-player-table-opponent");
        if (!container) throw new Error("Solo enemy container not found");

        const html = `
        <div class="swd-solo-enemy" data-faction="${data.faction.toLowerCase()}">
            <div class="swd-solo-enemy__left-side">
                <div class="swd-solo-enemy__reserves"></div>
                <div class="swd-solo-enemy__shuttles"></div>
            </div>
            <div class="swd-solo-enemy__center">
                <div class="swd-solo-enemy__progress"></div>
                <div class="swd-solo-enemy__active-base"></div>
                <div class="swd-solo-enemy__leader"></div>
                <div class="swd-solo-enemy__resources"></div>
            </div>
            <div class="swd-solo-enemy__right-side">
                <div class="swd-solo-enemy__muster">
                    <div class="swd-solo-enemy__muster-visible"></div>
                    <div class="swd-solo-enemy__muster-hidden2"></div>
                </div>
                <div class="swd-solo-enemy__play"></div>
            </div>
        </div>`;

        console.log("Enemy data", data);

        container.insertAdjacentHTML("beforeend", html);

        this.stocks = {
            leader: this.createStock(container, ".swd-solo-enemy__leader"),
            activeBase: this.createStock(container, ".swd-solo-enemy__active-base"),
            reserve: this.createStock(container, ".swd-solo-enemy__reserves"),
            shuttles: this.createStock(container, ".swd-solo-enemy__shuttles"),
            muster: this.createStock(container, ".swd-solo-enemy__muster-visible"),
            musterHidden: this.createStock(container, ".swd-solo-enemy__muster-hidden2"),
            play: this.createStock(container, ".swd-solo-enemy__play"),
        };

        this.resourcesElement = container.querySelector(".swd-solo-enemy__resources")!;
        this.progressElement = container.querySelector(".swd-solo-enemy__progress-value")!;
        this.progressTrackElement = container.querySelector(".swd-solo-enemy__progress-track")!;

        this.render(data);
    }

    public render(data: SoloEnemyData): void {
        this.setResources(data.resources);
        // this.setProgress(data.progress);
        this.replaceStock("leader", data.leader);
        this.replaceStock("activeBase", data.activeBase ? [data.activeBase] : []);
        this.replaceStock("shuttles", data.shuttles);
        this.replaceStock("muster", data.muster);
        this.replaceStock("musterHidden", data.musterHidden);
        this.replaceStock("play", data.playArea);
        this.replaceStock("reserve", data.reserve);
    }

    public async moveCard(card: Card, destination: string): Promise<void> {
        const target = this.zoneFor(destination);
        if (!target) return;

        const targetStock = this.stocks[target];
        await targetStock.addCard(card);
    }

    public async removeCard(card: Card): Promise<void> {
        for (const stock of Object.values(this.stocks)) {
            if (stock.getCards().some((candidate) => candidate.id === card.id)) {
                await stock.removeCard(card);
                return;
            }
        }
    }

    public setResources(value: number): void {
        this.resourcesElement.textContent = String(value);
    }

    public setProgress(value: number): void {
        // this.progressElement.textContent = String(value);
        // this.progressTrackElement.style.setProperty("--progress", String(Math.max(0, Math.min(10, value))));
    }

    public getAttackTargetStocks(): Array<InstanceType<typeof BgaCards.LineStock<Card>>> {
        return [this.stocks.leader, this.stocks.activeBase, this.stocks.play];
    }

    private createStock(container: Element, selector: string): InstanceType<typeof BgaCards.LineStock<Card>> {
        return new BgaCards.LineStock<Card>(
            this.game.cardManager,
            container.querySelector(selector)!,
            { center: true },
        );
    }

    private replaceStock(zone: SoloZone, cards: Card[]): void {
        const stock = this.stocks[zone];
        stock.removeAll();
        stock.addCards(cards);
    }

    private zoneFor(destination: string): SoloZone | null {
        switch (destination) {
            case "solo_enemy_leader": return "leader";
            case "solo_enemy_active_base": return "activeBase";
            case "solo_enemy_shuttles": return "shuttles";
            case "solo_enemy_muster_visible": return "muster";
            case "solo_enemy_muster_hidden": return "musterHidden";
            case "solo_enemy_play": return "play";
            case "solo_enemy_reserve": return "reserve";
            default: return null;
        }
    }
}
