const BgaAnimations = await globalThis.importEsmLib("bga-animations", "1.x");
const BgaCards = await globalThis.importEsmLib("bga-cards", "1.x");

class MyCardManager extends BgaCards.Manager {
    constructor(game, currentPlayer) {
        super({
            animationManager: game.animationManager,
            type: "card",
            getId: (card) => card.id.toString(),
            setupDiv: (card, cardDiv) => {
                if ("type" in card)
                    cardDiv.dataset.type = card.type.toLowerCase();
                if ("faction" in card) {
                    cardDiv.dataset.faction = card.faction;
                    cardDiv.dataset.isNeutral = card.faction === "Neutral" ? "true" : "false";
                    cardDiv.dataset.isAlly = card.faction !== "Neutral" && card.faction === this.currentPlayer.faction ? "true" : "false";
                    cardDiv.dataset.isEnemy = card.faction !== "Neutral" && card.faction !== this.currentPlayer.faction ? "true" : "false";
                }
            },
            setupFrontDiv: (card, frontDiv) => {
                frontDiv.dataset.img = card.img;
            },
            isCardVisible: (card) => "img" in card,
            cardBorderRadius: "8px",
            cardWidth: 120,
            cardHeight: 168,
        });
        this.game = game;
        this.currentPlayer = currentPlayer;
    }
    setCardAsSelected(card) {
        this.getCardElement(card)?.classList.add("bga-cards_selected-card");
    }
    removeAllCardsAsSelected() {
        document.querySelectorAll(".bga-cards_selected-card").forEach((cardElement) => {
            cardElement.classList.remove("bga-cards_selected-card");
        });
    }
}

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
    async notif_onPlayCard(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.playArea.addCard(args.card);
        this.game.gameui.wait(350);
    }
    async notif_onPlayerCounter(args) {
    }
    async notif_onPurchaseGalaxyCard(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.discard.addCard(args.card);
        this.game.gameui.wait(350);
    }
    async notif_onMoveCardToHand(args) {
        if (args.player_id !== this.game.players.getCurrentPlayerId())
            return;
        await this.game.playerHand.addCard(args.card);
        this.game.gameui.wait(350);
    }
    async notif_onDrawCards(args) {
        const table = this.game.getPlayerTable(args.player_id);
        const addedCards = args._private?.cards ?? args.cards;
        if (args.player_id === this.game.players.getCurrentPlayerId()) {
            await this.game.playerHand.addCards(addedCards, { fromStock: table.deck });
        }
        else {
            await table.deck.removeCards(addedCards, {
                slideTo: this.game.playerPanels.getElement(args.player_id),
                autoUpdateCardNumber: true,
            });
        }
        this.game.gameui.wait(350);
    }
}

class PlayerHand extends BgaCards.HandStock {
    constructor(game) {
        super(game.cardManager, document.querySelector(".swd-player-hand"), {
            cardOverlap: 50,
            emptyHandMessage: _("You have no cards in your hand"),
        });
    }
}

class PlayerTable {
    constructor(game, player, isCurrentPlayer) {
        this.game = game;
        this.player = player;
        this.isCurrentPlayer = isCurrentPlayer;
        this.playerId = Number(player.id);
        const color = `#${player.color}`;
        const html = `<div id="player-table-${this.playerId}" class="swd-player-table" data-player-id="${this.playerId}" style="--color-border: ${color};">
         <div class="swd-player-info">
            <div class="swd-player-name">${player.name}</div>
            <div class="swd-player-resources">Resources: <span id="player-resources-${this.playerId}"></span></div>
         </div>
         <div class="swd-player-area">
            <div class="swd-play-area"></div>
            <div class="swd-player-decks">
               <div class="swd-player-base-section">
                  <div class="swd-player-ships"></div>
                  <div class="swd-player-active-base"></div>
               </div>
               <div class="swd-player-deck"></div>
               <div class="swd-player-discard"></div>
            </div>
         </div>
      </div>`;
        const containerSelector = isCurrentPlayer ? ".swd-player-table-current" : ".swd-player-table-opponent";
        const container = document.querySelector(containerSelector);
        if (container) {
            container.insertAdjacentHTML("beforeend", html);
        }
        this.setupResourceCounter(player);
        this.setupPlayArea(player);
        this.setupDeckAndDiscard(player);
        this.setupBaseAndShips(player);
    }
    setupBaseAndShips(player) {
        this.activeBase = new BgaCards.LineStock(this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-player-active-base`), {
            center: true,
        });
        this.ships = new BgaCards.LineStock(this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-player-ships`), {
            center: true,
        });
        if (player.ships)
            this.ships.addCards(player.ships);
        if (player.activeBase)
            this.activeBase.addCard(player.activeBase);
    }
    setupDeckAndDiscard(player) {
        this.deck = new BgaCards.Deck(this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-player-deck`), {
            cardNumber: player.deckCount,
            counter: {
                show: true,
                size: 6,
                position: "bottom-right",
            },
            fakeCardGenerator: (deckId) => {
                return { id: this.playerId };
            },
        });
        this.discard = new BgaCards.Deck(this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-player-discard`), {
            autoRemovePreviousCards: false,
            counter: {
                show: true,
                size: 6,
                position: "bottom-right",
            },
        });
        this.discard.addCards(player.discard);
    }
    setupResourceCounter(player) {
        this.resourceCounter = new ebg.counter();
        this.resourceCounter.create(document.getElementById(`player-resources-${this.playerId}`), {
            value: player.resources,
            playerCounter: "resources",
            playerId: this.playerId,
        });
    }
    setupPlayArea(player) {
        this.playArea = new BgaCards.LineStock(this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-play-area`), {
            center: false,
            selectedCardStyle: {
                outlineColor: "#00FFFF",
            },
        });
        this.playArea.addCards(player.playAreaCards);
    }
}

class PlayerTurnActionSelectionState {
    constructor(game) {
        this.game = game;
    }
    onEnteringState(args, isCurrentPlayerActive) {
        if (!isCurrentPlayerActive)
            return;
        this.setupPlayerHandSelectableCards(args);
        this.setupGalaxyRowSelectableCards(args);
    }
    onLeavingState(isCurrentPlayerActive) {
        if (!isCurrentPlayerActive)
            return;
        this.game.playerHand.setSelectionMode("none");
        this.game.playerHand.onCardClick = undefined;
    }
    onUpdateActionButtons(args, isCurrentPlayerActive) {
    }
    setupPlayerHandSelectableCards(args) {
        const selectableCards = this.game.playerHand
            .getCards()
            .filter((card) => args.selectableCardIds.includes(card.id));
        this.game.playerHand.setSelectionMode("single");
        this.game.playerHand.setSelectableCards(selectableCards);
        this.game.playerHand.onCardClick = async (card) => {
            if (!args.selectableCardIds.includes(card.id))
                return;
            this.game.playerHand.unselectAll(true);
            if (this.game.gameui.isInterfaceLocked())
                return;
            await this.game.actions.performAction("actPlayCard", { cardId: card.id });
        };
    }
    setupGalaxyRowSelectableCards(args) {
        const galaxyRow = this.game.tableCenter.galaxyRow;
        const selectableCards = galaxyRow
            .getCards()
            .filter((card) => args.selectableGalaxyCardIds.includes(card.id));
        galaxyRow.setSelectionMode("single");
        galaxyRow.setSelectableCards(selectableCards);
        galaxyRow.onCardClick = async (card) => {
            galaxyRow.unselectCard(card, true);
            await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
        };
    }
}

class PlayerTurnAskChoiceState {
    constructor(game) {
        this.game = game;
    }
    onEnteringState(args, isCurrentPlayerActive) {
        this.game.cardManager.setCardAsSelected(args.card);
    }
    onLeavingState(isCurrentPlayerActive) { }
    onUpdateActionButtons(args, isCurrentPlayerActive) {
        if (!isCurrentPlayerActive)
            return;
        Object.entries(args.options).forEach(([optionId, option]) => {
            const handle = async () => {
                await this.game.actions.performAction("actMakeChoice", { choiceId: Number(optionId) });
            };
            this.game.statusBar.addActionButton(_(option), handle);
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
        this.states["playerTurnActionSelection"] = new PlayerTurnActionSelectionState(this.game);
        this.states["playerTurnAskChoice"] = new PlayerTurnAskChoiceState(this.game);
    }
    onEnteringState(stateName, args) {
        debugLog("Entering state:", stateName, args.args);
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
        this.game.cardManager.removeAllCardsAsSelected();
    }
    onUpdateActionButtons(stateName, args) {
        debugLog("Updating action buttons for state:", stateName, args);
        const state = this.states[stateName];
        if (state && state.onUpdateActionButtons) {
            state.onUpdateActionButtons(args, this.game.players.isCurrentPlayerActive());
        }
    }
}

class TableCenter {
    constructor(game) {
        this.game = game;
        this.game.gameArea
            .getElement()
            .querySelector(".swd-table-center")
            .insertAdjacentHTML("beforeend", `<div>
               <div class="galaxy-row-label">Galaxy Row</div>
               <div class="galaxy-row-wrapper">
                  <div>
                     <div class="galaxy-decks">
                        <div class="deck-draw-pile"></div>
                        <div class="force-track">
                           <div class="force-track-background"></div>
                           <div class="force-track-indicator" data-force="${game.gamedatas.force}"></div>
                        </div>
                        <div class="deck-discard-pile"></div>
                     </div>
                  </div>
                  <div>
                     <div class="galaxy-row" id="galaxy-row"></div>
                  </div>
               </div>
            </div>`);
        this.galaxyRow = new BgaCards.LineStock(game.cardManager, document.getElementById("galaxy-row"), {
            gap: '12px',
        });
        this.galaxyDeck = new BgaCards.Deck(game.cardManager, document.querySelector(".deck-draw-pile"), {
            cardNumber: game.gamedatas.galaxyDeckCount,
            counter: {
                show: true,
                size: 6,
                position: 'bottom-right',
            }
        });
        this.galaxyDiscard = new BgaCards.Deck(game.cardManager, document.querySelector(".deck-discard-pile"), {
            autoRemovePreviousCards: false,
            counter: {
                show: true,
                size: 6,
                position: 'bottom-right',
            }
        });
        this.galaxyRow.addCards(game.gamedatas.galaxyRow);
        this.galaxyDiscard.addCards(game.gamedatas.galaxyDiscard);
    }
}

class Game {
    constructor(bga) {
        this.playerTables = [];
        Object.assign(this, bga);
        Object.assign(this.gameui, { game: this });
        this.gameArea.getElement().insertAdjacentHTML("beforeend", `<div class="swd-table-wrapper">
            <div class="swd-player-table-opponent"></div>
            <div class="swd-table-center"></div>
            <div class="swd-player-table-current"></div>
            <div class="swd-player-hand"></div>
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
        const orderedPlayers = this.getOrderedPlayers();
        this.cardManager = new MyCardManager(this, orderedPlayers[0]);
        this.tableCenter = new TableCenter(this);
        this.playerTables = this.createPlayerTables();
        this.setupPlayerHand();
        this.setupNotifications();
    }
    getOrderedPlayers() {
        const players = Object.values(this.gamedatas.players).sort((a, b) => a.playerNo - b.playerNo);
        const playerIndex = players.findIndex((player) => Number(player.id) === Number(this.gameui.player_id));
        return playerIndex > 0 ? [...players.slice(playerIndex), ...players.slice(0, playerIndex)] : players;
    }
    getPlayerTable(playerId) {
        const playerTable = this.playerTables.find((table) => table.playerId === playerId);
        if (!playerTable) {
            throw new Error(`Player table not found for player ID: ${playerId}`);
        }
        return playerTable;
    }
    setupNotifications() {
        this.notificationManager = new NotificationManager(this);
        this.notificationManager.setup();
    }
    createPlayerTables() {
        const orderedPlayers = this.getOrderedPlayers();
        return orderedPlayers.map((player, index) => {
            const isCurrentPlayer = index === 0;
            return new PlayerTable(this, player, isCurrentPlayer);
        });
    }
    setupPlayerHand() {
        if (this.players.isCurrentPlayerSpectator())
            return;
        this.playerHand = new PlayerHand(this);
        if (this.gamedatas.playerHand) {
            this.playerHand.addCards(this.gamedatas.playerHand);
        }
    }
}

export { Game };
