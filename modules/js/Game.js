const BgaAnimations = await globalThis.importEsmLib("bga-animations", "1.x");
const BgaCards = await globalThis.importEsmLib("bga-cards", "1.x");
const [tippy] = await globalThis.importDojoLibs([g_gamethemeurl + 'modules/js/vendor/tippy-headless.min.js']);

class TooltipManager {
    constructor(game) {
        this.game = game;
        this.tooltips = {};
    }
    addTooltip(element, card, placement = 'auto') {
        if (this.game.userPreferences.get(200) !== 0)
            return;
        if (this.tooltips[element.id] !== undefined) {
            this.tooltips[element.id].destroy();
        }
        const content = this.getTooltip(element.id, card);
        this.tooltips[element.id] = tippy(element, {
            content,
            placement,
            touch: ['hold', 400],
            delay: [500, 100],
            appendTo: document.getElementById('swd-table-wrapper'),
            render(instance) {
                const popper = document.createElement('div');
                const arrow = document.createElement('arrow');
                arrow.dataset.popperArrow = 'true';
                arrow.classList.add('tooltip-arrow');
                popper.appendChild(arrow);
                const box = document.createElement('div');
                popper.appendChild(box);
                popper.className = 'swd-tooltip';
                box.insertAdjacentHTML('beforeend', instance.props.content);
                function onUpdate(prevProps, nextProps) {
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
    getTooltip(id, card) {
        return `<div id="${id}-tooltip" class="swd-card-tooltip">
         <div class="card-tooltip-frame">${this.getTooltipCard(card)}</div>
         <div class="tooltip-explanation">${this.getExplanation(card)}</div>
      </div>`;
    }
    getTooltipCard(card) {
        if (!("type" in card))
            debugger;
        return `
         <div class="bga-cards_card card" data-type="${card.type.toLowerCase()}" data-faction="${card.faction}">
            <div class="bga-cards_card-sides">
               <div class="bga-cards_card-side front" data-img="${card.img}"></div>
            </div>
         </div>`;
    }
    getExplanation(card) {
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

class MyCardManager extends BgaCards.Manager {
    constructor(game, currentPlayer, type = "card") {
        super({
            animationManager: game.animationManager,
            type: type,
            getId: (card) => card.id.toString(),
            setupDiv: (card, cardDiv) => {
                if ("type" in card)
                    cardDiv.dataset.type = card.type.toLowerCase();
                if ("faction" in card) {
                    cardDiv.dataset.faction = card.faction;
                    cardDiv.dataset.isNeutral = card.faction === "Neutral" ? "true" : "false";
                    cardDiv.dataset.isAlly =
                        card.faction !== "Neutral" && card.faction === this.currentPlayer.faction ? "true" : "false";
                    cardDiv.dataset.isEnemy =
                        card.faction !== "Neutral" && card.faction !== this.currentPlayer.faction ? "true" : "false";
                }
            },
            setupFrontDiv: (card, frontDiv) => {
                frontDiv.dataset.img = card.img;
                if ("type" in card) {
                    this.tooltipManager.addTooltip(frontDiv, card);
                }
                if ("damage" in card && card.damage > 0) {
                    this.setDamageOnCard(card);
                }
            },
            isCardVisible: (card) => "img" in card,
            cardBorderRadius: "8px",
            cardWidth: 120,
            cardHeight: 168,
        });
        this.game = game;
        this.currentPlayer = currentPlayer;
        this.type = type;
        this.tooltipManager = new TooltipManager(this.game);
    }
    setCardAsSelected(card) {
        if (!card) {
            console.warn("setCardAsSelected called with null card");
            return;
        }
        this.getCardElement(card)?.classList.add("bga-cards_selected-card");
    }
    removeAllCardsAsSelected() {
        document.querySelectorAll(".bga-cards_selected-card").forEach((cardElement) => {
            cardElement.classList.remove("bga-cards_selected-card");
        });
    }
    setDamageOnCard(card) {
        const cardElement = this.getCardElement(card);
        if (!cardElement)
            return;
        let damageDiv = cardElement.querySelector(".card-damage");
        if (!damageDiv) {
            damageDiv = document.createElement("div");
            damageDiv.classList.add("card-damage");
            cardElement.querySelector(".bga-cards_card-sides").appendChild(damageDiv);
        }
        damageDiv.innerText = (card.health - card.damage).toString();
    }
}
class DiscardCardManager extends BgaCards.Manager {
    constructor(game) {
        super({
            animationManager: new BgaAnimations.Manager({
                animationsActive: () => false,
            }),
            type: "discard",
            getId: (card) => card.id.toString(),
            setupDiv: (card, cardDiv) => {
                if ("type" in card)
                    cardDiv.dataset.type = card.type.toLowerCase();
                if ("faction" in card) {
                    cardDiv.dataset.faction = card.faction;
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
    }
}

class NotificationManager {
    constructor(game) {
        this.game = game;
    }
    setup() {
        this.game.notifications.setupPromiseNotifications({ handlers: [this], logger: console.log });
    }
    async notif_onPlayCard(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.playArea.addCard(args.card);
    }
    async notif_onPlayCardToShipArea(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.ships.addCard(args.card, {
            parallelAnimations: [
                {
                    keyframes: [{ transform: "rotate(90deg)" }],
                },
            ],
            duration: 500,
        });
    }
    async notif_setPlayerCounter(args) {
    }
    notif_setTableCounter(args) {
        if (args.name === "force") {
            this.game.tableCenter.setForceCounter(args.value);
        }
    }
    async notif_onPurchaseGalaxyCard(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.discard.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onMoveCardToHand(args) {
        if (args.player_id !== this.game.players.getCurrentPlayerId())
            return;
        await this.game.playerHand.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onDrawCards(args) {
        const table = this.game.getPlayerTable(args.player_id);
        const addedCards = args._private?.cards ?? args.cards;
        if (args.player_id === this.game.players.getCurrentPlayerId()) {
            await this.game.playerHand.addCards(addedCards, { fromStock: table.deck }, true);
        }
        else {
            await table.deck.removeCards(addedCards, {
                slideTo: this.game.playerPanels.getElement(args.player_id),
                autoUpdateCardNumber: true,
            });
        }
        await this.game.gameui.wait(350);
    }
    notif_onDealDamageToCard(args) {
        this.game.cardManager.setDamageOnCard(args.card);
    }
    notif_onRepairDamageBase(args) {
        this.game.cardManager.setDamageOnCard(args.card);
    }
    async notif_onDiscardCards(args) {
        const cards = args.cards.sort((a, b) => a.locationArg - b.locationArg);
        switch (args.destination) {
            case "player_discard":
                const table = this.game.getPlayerTable(args.player_id);
                await table.discard.addCards(cards, {}, 200);
                break;
            case "galaxy_discard":
                const galaxyDiscard = this.game.tableCenter.galaxyDiscard;
                await galaxyDiscard.addCards(cards, {}, 200);
                break;
            default:
                debugger;
                this.game.dialogs.showMessage("Unknown destination for discarding cards: " + args.destination, "error");
                return;
        }
        await this.game.gameui.wait(350);
    }
    async notif_onShuffleDiscardIntoDeck(args) {
        const table = this.game.getPlayerTable(args.player_id);
        const cards = table.discard.getCards().map((card) => ({ id: card.id }));
        await table.deck.addCards(cards);
        await table.deck.shuffle();
    }
    async notif_onRefillGalaxyRow(args) {
        const fromStock = this.game.tableCenter.galaxyDeck;
        await this.game.tableCenter.galaxyRow.addCards(args.newCards, { fromStock }, 300);
    }
    async notif_onDiscardGalaxyCard(args) {
        await this.game.tableCenter.galaxyDiscard.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onExileCard(args) {
        const stock = this.game.cardManager.getCardStock(args.card);
        if (stock) {
            const slideTo = this.game.tableCenter.galaxyDiscard.element;
            await stock.removeCard(args.card, { slideTo });
        }
    }
    async notif_onNewBase(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.activeBase.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onMoveCardToTopOfDeck(args) {
        switch (args.destination) {
            case "player_deck":
                const table = this.game.getPlayerTable(args.player_id);
                const card = { ...args.card };
                delete card.img;
                await table.deck.addCard(card, { finalSide: "back", initialSide: "front" });
                await this.game.gameui.wait(350);
                break;
            default:
                this.game.dialogs.showMessage("Unknown destination for moving card to top of deck: " + args.destination, "error");
                break;
        }
    }
    async notif_onMoveCardToDiscard(args) {
        const table = this.game.getPlayerTable(args.player_id);
        await table.discard.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onMoveCardToGalaxyDiscard(args) {
        await this.game.tableCenter.galaxyDiscard.addCard(args.card);
        await this.game.gameui.wait(350);
    }
    async notif_onRevealTopCard(args) {
        switch (args.from) {
            case "deck":
                const deck = this.game.tableCenter.galaxyDeck;
                deck.setCardVisible(args.card, false, { updateFront: true, updateFrontDelay: 0 });
                await this.game.gameui.wait(500);
                deck.flipCard(args.card);
                break;
            default:
                this.game.dialogs.showMessage("Unknown zone for revealing card: " + args.from, "error");
                break;
        }
    }
    async notif_onMoveCardToGalaxyRow(args) {
        await this.game.tableCenter.galaxyRow.addCard(args.card);
    }
    async notif_onMoveCardToGalaxyDeck(args) {
        const deck = this.game.tableCenter.galaxyDeck;
        const card = { ...args.card };
        delete card.img;
        await deck.addCard(card, { finalSide: "back", initialSide: "front" });
        await this.game.gameui.wait(350);
    }
    async notif_onHideCards(args) {
        for (const cardId of args.cardIds) {
            const cardTemp = { id: cardId };
            const stock = this.game.cardManager.getCardStock(cardTemp);
            stock.setCardVisible(cardTemp, false, { updateFront: true, updateFrontDelay: 0 });
        }
    }
}

class PlayerHand extends BgaCards.HandStock {
    constructor(game) {
        super(game.cardManager, document.querySelector(".swd-player-hand"), {
            cardOverlap: 50,
            emptyHandMessage: _("You have no cards in your hand"),
            floatZIndex: 5,
        });
    }
}

class DiscardWithPopup extends BgaCards.Deck {
    constructor(game, cardManager, node, options) {
        super(cardManager, node, {
            ...options,
            autoUpdateCardNumber: false,
            fakeCardGenerator: (deckId) => {
                const cards = this.getCards();
                return cards[cards.length - 1] || { id: deckId };
            },
        });
        this.game = game;
        this.cardManager = cardManager;
        this.lineDiscard = null;
        node.classList.add("discard-with-popup");
        node.insertAdjacentHTML("afterbegin", `<div class="swd-special-discard">${_("View Discard")}</div>`);
        const el = node.querySelector(".swd-special-discard");
        el.onclick = this.displayDiscardOverlay.bind(this);
    }
    addCard(card, settings) {
        const addPromise = super.addCard(card, settings);
        addPromise.then((added) => {
            this.setCardNumber(this.getCards().length);
        });
        return addPromise;
    }
    removeCard(card, settings) {
        const removePromise = super.removeCard(card, settings);
        removePromise.then((removed) => {
            this.setCardNumber(this.getCards().length);
        });
        return removePromise;
    }
    async displayDiscardOverlay() {
        const html = `
         <div class="swd-discard-overlay visible">
            <div class="swd-discard-overlay-content">
               <div class="swd-discard-overlay-header">
                  <span class="swd-discard-overlay-title">${_("Discard Pile")}</span>
                  <span class="swd-discard-overlay-close">&times;</span>
               </div>
               <div class="swd-discard-overlay-body">
                  <div class="swd-discard-overlay-cards"></div>
               </div>
            </div>
         </div>`;
        document.querySelector(".swd-table-center").insertAdjacentHTML("afterbegin", html);
        const closeBtn = document.querySelector(".swd-discard-overlay-close");
        closeBtn.onclick = this.closePopup.bind(this);
        this.lineDiscard = new BgaCards.LineStock(this.game.discardCardManager, document.querySelector(".swd-discard-overlay-cards"), {
            center: true,
            gap: "15px",
            selectedCardStyle: {
                outlineColor: "#00FFFF",
            },
        });
        const cards = this.getCards().map((card) => ({ ...card }));
        await this.lineDiscard.addCards(cards, {}, false);
        this.lineDiscard.setSelectionMode(this.selectionMode);
        this.lineDiscard.setSelectableCards(this.selectableCards);
        this.lineDiscard.onCardClick = (card) => {
            this.onCardClick?.(card);
        };
        this.lineDiscard.onSelectionChange = (selection, lastChange) => {
            this.onSelectionChange?.(selection, lastChange);
        };
        super.getSelection().forEach((card) => this.lineDiscard?.selectCard(card, true));
    }
    closePopup() {
        const selection = this.lineDiscard?.getSelection();
        if (this.lineDiscard) {
            this.lineDiscard.onCardClick = undefined;
            this.lineDiscard.onSelectionChange = undefined;
            this.lineDiscard.removeAll();
            this.game.discardCardManager.removeStock(this.lineDiscard);
            this.lineDiscard = null;
        }
        const overlay = document.querySelector(".swd-discard-overlay");
        overlay?.remove();
        super.unselectAll(true);
        selection?.forEach((card) => super.selectCard(card, true));
    }
    getSelection() {
        return this.lineDiscard ? this.lineDiscard.getSelection() : super.getSelection();
    }
}

class PlayerTable {
    constructor(game, player, isCurrentPlayer, index) {
        this.game = game;
        this.player = player;
        this.isCurrentPlayer = isCurrentPlayer;
        this.index = index;
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
               <div class="swd-player-deck" id="player-deck-${this.playerId}"></div>
               <div class="swd-player-discard" id="player-discard-${this.playerId}"></div>
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
    onLeaveState() {
        [this.activeBase, this.ships, this.playArea, this.discard].forEach((stock) => {
            stock.setSelectionMode("none");
            stock.onCardClick = undefined;
            stock.onSelectionChange = undefined;
        });
        if (this.game.players.isCurrentPlayerActive()) {
            this.discard.closePopup();
        }
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
        });
        this.discard = new DiscardWithPopup(this.game, this.game.cardManager, document.querySelector(`#player-table-${this.playerId} .swd-player-discard`), {
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

class BaseState {
    constructor(game) {
        this.game = game;
    }
    onLeavingState(isCurrentPlayerActive) {
        this.game.cardManager.removeAllCardsAsSelected();
        this.game.playerTables.forEach((table) => table.onLeaveState());
        this.game.tableCenter.onLeaveState();
        this.game.playerHand.setSelectionMode("none");
        this.game.playerHand.onCardClick = undefined;
        this.game.playerHand.onSelectionChange = undefined;
    }
}

class EffectCardSelectionState extends BaseState {
    onEnteringState(args, isCurrentPlayerActive) {
        this.game.cardManager.setCardAsSelected(args.card);
        this.displayDescription(args, isCurrentPlayerActive);
        if (!isCurrentPlayerActive)
            return;
        this.game.statusBar.removeActionButtons();
        this.addConfirmButton(args);
        const stocks = this.getStocks(args);
        stocks.forEach((stock) => {
            stock.setSelectionMode(args.nbr > 1 ? "multiple" : "single");
            stock.setSelectableCards(args.selectableCards);
            stock.onSelectionChange = () => {
                const selectedCards = this.getSelectedCards(args);
                const btnConfirm = document.getElementById("btn-confirm");
                if (args.optional) {
                    btnConfirm.disabled = selectedCards.length > args.nbr;
                }
                else {
                    btnConfirm.disabled = selectedCards.length !== args.nbr;
                }
            };
        });
    }
    onPlayerActivationChange(args, isCurrentPlayerActive) {
        this.onEnteringState(args, isCurrentPlayerActive);
    }
    addConfirmButton(args) {
        const handleConfirm = async () => {
            const selectedCards = this.getSelectedCards(args);
            await this.game.actions.performAction("actCardSelection", {
                cardIds: selectedCards.map((card) => card.id),
            });
        };
        this.game.statusBar.addActionButton(_("Confirm"), handleConfirm, {
            disabled: !args.optional,
            id: "btn-confirm",
        });
    }
    displayDescription(args, isCurrentPlayerActive) {
        if (isCurrentPlayerActive) {
            this.game.statusBar.setTitle(args.descriptionMyTurn, args);
        }
        else {
            this.game.statusBar.setTitle(args.description, args);
        }
    }
    getSelectedCards(args) {
        const stocks = this.getStocks(args);
        let selectedCards = [];
        stocks.forEach((stock) => {
            if (!stock)
                return;
            selectedCards = selectedCards.concat(stock.getSelection());
        });
        return selectedCards;
    }
    getStocks(args) {
        let stocks = args.selectableCards.map((card) => {
            return this.game.cardManager.getCardStock(card);
        });
        stocks = stocks.filter((stock) => !!stock);
        return Array.from(new Set(stocks));
    }
}

class PlayerTurnActionSelectionState extends BaseState {
    onEnteringState(args, isCurrentPlayerActive) {
        if (!isCurrentPlayerActive)
            return;
        if (args.canCommitAttack) {
            const handle = async () => await this.game.actions.performAction("actCommitAttack");
            this.game.statusBar.addActionButton(_("Commit to an attack"), handle);
        }
        const handleEndTurn = async () => await this.game.actions.performAction("actEndTurn");
        this.game.statusBar.addActionButton(_("End Turn"), handleEndTurn, {
            color: "alert",
            confirm: () => {
                if (args.selectableCardIds.length > 0) {
                    return _("You have playable cards in your hand. Are you sure you want to end your turn?");
                }
                else if (args.selectableGalaxyCardIds.length > 0) {
                    return _("You have purchasable cards in the Galaxy Row and resources available. Are you sure you want to end your turn?");
                }
                else if (args.canCommitAttack) {
                    return _("You can still commit to an attack. Are you sure you want to end your turn?");
                }
                return null;
            },
        });
        this.setupPlayerHandSelectableCards(args);
        this.setupGalaxyRowSelectableCards(args);
        this.setupPlayerPlayAreaSelectableCards(args);
        this.setupOuterRimSelectableCards(args);
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
        const selectableCards = galaxyRow.getCards().filter((card) => args.selectableGalaxyCardIds.includes(card.id));
        galaxyRow.setSelectionMode("single");
        galaxyRow.setSelectableCards(selectableCards);
        galaxyRow.onCardClick = async (card) => {
            galaxyRow.unselectCard(card, true);
            await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
        };
    }
    setupOuterRimSelectableCards(args) {
        const outerRimDeck = this.game.tableCenter.outerRimDeck;
        const selectableCards = outerRimDeck.getCards().filter((card) => args.selectableGalaxyCardIds.includes(card.id));
        outerRimDeck.setSelectionMode("single");
        outerRimDeck.setSelectableCards(selectableCards);
        outerRimDeck.onCardClick = async (card) => {
            outerRimDeck.unselectCard(card, true);
            await this.game.actions.performAction("actPurchaseGalaxyCard", { cardId: card.id });
        };
    }
    setupPlayerPlayAreaSelectableCards(args) {
        const { playArea, ships, activeBase } = this.game.getCurrentPlayerTable();
        [playArea, ships, activeBase].forEach((area) => {
            const selectableCards = area.getCards().filter((card) => args.selectableAbilityCardIds.includes(card.id));
            if (selectableCards.length === 0)
                return;
            area.setSelectionMode("single");
            area.setSelectableCards(selectableCards);
            area.onCardClick = async (card) => {
                area.unselectCard(card, true);
                await this.game.actions.performAction("actUseCardAbility", { cardId: card.id });
            };
        });
    }
}

class PlayerTurnAskChoiceState extends BaseState {
    onEnteringState(args, isCurrentPlayerActive) {
        this.game.cardManager.setCardAsSelected(args.card);
        this.game.statusBar.removeActionButtons();
        if (!isCurrentPlayerActive)
            return;
        Object.entries(args.options).forEach(([optionId, option]) => {
            const handle = async () => {
                await this.game.actions.performAction("actMakeChoice", { choiceId: Number(optionId) });
            };
            const label = this.game.gameui.format_string(option.label, option.labelArgs ?? {});
            this.game.statusBar.addActionButton(label, handle);
        });
    }
    onPlayerActivationChange(args, isCurrentPlayerActive) {
        this.onEnteringState(args, isCurrentPlayerActive);
    }
}

class PlayerTurnAttackCommitState extends BaseState {
    onEnteringState(args, isCurrentPlayerActive) {
        this.game.cardManager.setCardAsSelected(args.target);
        this.addConfirmButton();
        this.addCancelButton();
        const activePlayerId = this.game.players.getActivePlayerId();
        const playArea = this.game.getPlayerTable(activePlayerId).playArea;
        playArea.setSelectionMode("multiple");
        playArea.setSelectableCards(args.attackers);
        if (isCurrentPlayerActive) {
            playArea.onSelectionChange = (selection) => {
                const btnConfirm = document.getElementById("btn-confirm-attackers");
                btnConfirm.disabled = selection.length === 0;
            };
            args.attackers.forEach((card) => playArea.selectCard(card));
        }
    }
    addConfirmButton() {
        const handleConfirm = async () => {
            const selectedCards = this.game.getCurrentPlayerTable().playArea.getSelection();
            const cardIds = selectedCards.map((card) => card.id);
            await this.game.actions.performAction("actCommitAttack", { cardIds });
        };
        this.game.statusBar.addActionButton(_("Confirm Attackers"), handleConfirm, {
            disabled: true,
            id: "btn-confirm-attackers",
        });
    }
    addCancelButton() {
        const handleCancel = async () => this.game.actions.performAction("actCancel");
        this.game.statusBar.addActionButton(_("Cancel"), handleCancel, {
            color: "alert",
        });
    }
}

class PlayerTurnAttackDeclarationState extends BaseState {
    onEnteringState(args, isCurrentPlayerActive) {
        const stocks = this.game.playerTables.flatMap((table) => [table.activeBase, table.ships]);
        stocks.push(this.game.tableCenter.galaxyRow);
        stocks.forEach((stock) => {
            stock.setSelectionMode("single");
            stock.setSelectableCards(args.targets);
            stock.onCardClick = async (card) => {
                stock.unselectCard(card, true);
                if (isCurrentPlayerActive && args.targets.find((c) => c.id === card.id)) {
                    await this.game.actions.performAction("actDeclareAttack", { cardId: card.id });
                }
            };
        });
        const handleCancel = async () => this.game.actions.performAction("actCancel");
        this.game.statusBar.addActionButton(_('Cancel'), handleCancel, {
            color: 'alert'
        });
    }
}

class PlayerTurnStartTurnBaseState extends BaseState {
    constructor(game) {
        super(game);
        this.bases = new BgaCards.LineStock(this.game.cardManager, document.querySelector(".swd-base-selection"), {
            gap: '20px',
        });
    }
    onEnteringState(args, isCurrentPlayerActive) {
        this.bases.addCards(args.selectableBases, { animationsActive: false });
        if (!isCurrentPlayerActive)
            return;
        this.bases.setSelectionMode("single");
        this.bases.setSelectableCards(args.selectableBases);
        this.bases.onCardClick = (card) => {
            this.game.actions.performAction("actSelectBase", { cardId: card.id });
        };
    }
    onLeavingState(isCurrentPlayerActive) {
        this.bases.removeAll();
        this.bases.onCardClick = undefined;
        super.onLeavingState(isCurrentPlayerActive);
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
                        <div id="deck-draw-pile" class="deck-draw-pile"></div>
                        <div id="deck-discard-pile" class="deck-discard-pile"></div>
                        <div class="force-track">
                           <div class="force-track-background"></div>
                           <div class="force-track-indicator" data-force="${game.gamedatas.force}"></div>
                        </div>
                        <div id="deck-outer-rim" class="deck-outer-rim"></div>
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
            autoRemovePreviousCards: false,
            counter: {
                show: true,
                size: 6,
                position: 'bottom-right',
            }
        });
        this.galaxyDiscard = new DiscardWithPopup(game, game.cardManager, document.querySelector(".deck-discard-pile"), {
            autoRemovePreviousCards: false,
            counter: {
                show: true,
                size: 6,
                position: 'bottom-right',
            }
        });
        this.outerRimDeck = new BgaCards.Deck(game.cardManager, document.querySelector(".deck-outer-rim"), {
            autoRemovePreviousCards: false,
            fakeCardGenerator: (deckId) => this.outerRimDeck.getCards().pop(),
            counter: {
                show: true,
                size: 6,
                position: 'bottom-right',
            }
        });
        this.galaxyRow.addCards(game.gamedatas.galaxyRow);
        this.galaxyDiscard.addCards(game.gamedatas.galaxyDiscard);
        this.outerRimDeck.addCards(game.gamedatas.outerRimDeck);
        this.galaxyDeck.addCards(game.gamedatas.galaxyDeck);
        if (game.gamedatas.galaxyDeckRevealedCard) {
            this.galaxyDeck.setCardVisible(game.gamedatas.galaxyDeckRevealedCard, true);
        }
    }
    onLeaveState() {
        [this.galaxyRow, this.galaxyDeck, this.galaxyDiscard, this.outerRimDeck].forEach((stock) => {
            stock.setSelectionMode("none");
            stock.onCardClick = undefined;
            stock.onSelectionChange = undefined;
        });
    }
    setForceCounter(value) {
        const indicator = this.game.gameArea
            .getElement()
            .querySelector(".force-track-indicator");
        if (!indicator)
            return;
        indicator.setAttribute("data-force", value.toString());
    }
}

class Game {
    constructor(bga) {
        this.playerTables = [];
        Object.assign(this, bga);
        Object.assign(this.gameui, { game: this });
        this.gameArea.getElement().insertAdjacentHTML("beforeend", `<div class="swd-table-wrapper" id="swd-table-wrapper">
            <div class="swd-base-selection"></div>
            <div class="swd-player-table-opponent"></div>
            <div class="swd-table-center"></div>
            <div class="swd-player-table-current"></div>
            <div class="swd-player-hand"></div>
          </div>`);
        this.animationManager = new BgaAnimations.Manager({
            animationsActive: () => this.gameui.bgaAnimationsActive(),
        });
    }
    setup(gamedatas) {
        this.gamedatas = gamedatas;
        const orderedPlayers = this.getOrderedPlayers();
        this.cardManager = new MyCardManager(this, orderedPlayers[0]);
        this.discardCardManager = new DiscardCardManager(this);
        this.tableCenter = new TableCenter(this);
        this.playerTables = this.createPlayerTables();
        this.setupPlayerHand();
        this.registerStates();
        this.setupNotifications();
    }
    registerStates() {
        this.states.logger = console.log;
        this.states.register("playerTurnActionSelection", new PlayerTurnActionSelectionState(this));
        this.states.register("playerTurnAskChoice", new PlayerTurnAskChoiceState(this));
        this.states.register("playerTurnAttackDeclaration", new PlayerTurnAttackDeclarationState(this));
        this.states.register("playerTurnAttackCommit", new PlayerTurnAttackCommitState(this));
        this.states.register("effectCardSelection", new EffectCardSelectionState(this));
        this.states.register("playerTurnStartTurnBase", new PlayerTurnStartTurnBaseState(this));
    }
    getCurrentPlayerTable() {
        return this.getPlayerTable(this.players.getCurrentPlayerId());
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
            return new PlayerTable(this, player, isCurrentPlayer, index);
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
    closeDiscardPopupIfNeeded() {
        this.playerTables.forEach((playerTable) => {
            playerTable.discard.closePopup();
        });
    }
    bgaFormatText(log, args) {
        try {
            if (log && args && !args.processed) {
                args.processed = true;
                ["card_name"].forEach((field) => {
                    if (args[field] !== null && args[field] !== undefined) {
                        args[field] = `<strong>${_(args[field])}</strong>`;
                    }
                });
                ["card_names"].forEach((field) => {
                    if (args[field] !== null && args[field] !== undefined && Array.isArray(args[field])) {
                        args[field] = args[field].map((name) => `<strong>${_(name)}</strong>`).join(", ");
                    }
                });
            }
        }
        catch (e) {
            console.error(log, args, "Exception thrown", e.stack);
        }
        return { log, args };
    }
}

export { Game };
