const BgaAnimations = await globalThis.importEsmLib("bga-animations", "1.x");
await globalThis.importEsmLib("bga-cards", "1.x");

class NotificationManager {
    constructor(game) {
        this.game = game;
    }
    setup() {
        this.game.notifications.setupPromiseNotifications({ handlers: [this] });
        const getNotifs = () => {
            return Object.getOwnPropertyNames(Object.getPrototypeOf(this))
                .filter((prop) => prop.startsWith("notif_") && typeof this[prop] === "function")
                .map((prop) => prop.slice(6));
        };
        ["message", ...getNotifs()].forEach((eventName) => {
            this.game.gameui.notifqueue.setIgnoreNotificationCheck(eventName, (notif) => notif.args.excluded_player_id && notif.args.excluded_player_id == this.game.players.getCurrentPlayerId());
        });
    }
}

const isDebug = window.location.host == "studio.boardgamearena.com" || window.location.hash.indexOf("debug") > -1;
const debugLog = (...args) => {
    if (isDebug)
        console.log(...args);
};

class StateManager {
    constructor(game) {
        this.game = game;
        this.states = {};
    }
    onEnteringState(stateName, args) {
        debugLog("Entering state:", stateName, args);
        const state = this.states[stateName];
        if (state && state.onEnteringState) {
            state.onEnteringState(args.args, this.game.players.isCurrentPlayerActive());
        }
    }
    onLeavingState(stateName) {
        debugLog("Leaving state:", stateName);
        const state = this.states[stateName];
        if (state && state.onLeavingState) {
            state.onLeavingState(this.game.players.isCurrentPlayerActive());
        }
    }
    onUpdateActionButtons(stateName, args) {
        debugLog("Updating action buttons for state:", stateName, args);
        const state = this.states[stateName];
        if (state && state.onUpdateActionButtons) {
            state.onUpdateActionButtons(args.args, this.game.players.isCurrentPlayerActive());
        }
    }
}

class Game {
    constructor(bga) {
        Object.assign(this, bga);
        this.gameArea.getElement().insertAdjacentHTML("beforeend", `<div class="swd-table-wrapper">
          </div>`);
        this.animationManager = new BgaAnimations.Manager({
            animationsActive: () => this.gameui.bgaAnimationsActive(),
        });
        this.stateManager = new StateManager(this);
        this.onEnteringState = this.stateManager.onEnteringState.bind(this.stateManager);
        this.onLeavingState = this.stateManager.onLeavingState.bind(this.stateManager);
        this.onUpdateActionButtons = this.stateManager.onUpdateActionButtons?.bind(this.stateManager);
    }
    setup(gamedatas) {
        this.gamedatas = gamedatas;
        this.setupNotifications();
    }
    getOrderedPlayers() {
        const players = Object.values(this.gamedatas.players).sort((a, b) => a.playerNo - b.playerNo);
        const playerIndex = players.findIndex((player) => Number(player.id) === Number(this.gameui.player_id));
        return playerIndex > 0 ? [...players.slice(playerIndex), ...players.slice(0, playerIndex)] : players;
    }
    setupNotifications() {
        this.notificationManager = new NotificationManager(this);
        this.notificationManager.setup();
    }
}

export { Game };
