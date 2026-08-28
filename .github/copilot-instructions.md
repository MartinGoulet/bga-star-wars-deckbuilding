---
description: "Instructions de contribution pour Star Wars: The Deckbuilding Game, jeu Board Game Arena avec backend PHP et client TypeScript/Sass."
---

# Star Wars: The Deckbuilding Game - Instructions Copilot

## Contexte du projet

- Star Wars: The Deckbuilding Game est une implementation pour Board Game Arena (BGA), identifiee techniquement par `starwarsdeckbuilding`.
- Le backend est en PHP dans `modules/php/`, avec le namespace `Bga\Games\StarWarsDeckbuilding`.
- La logique de jeu est repartie entre `modules/php/Game.php`, les contextes et resolvers de `modules/php/Core/`, le repository de cartes, les effets, le ciblage et les classes d'etat de `modules/php/States/`.
- Les constantes d'etat, de cartes, de factions, d'effets et de variables globales sont dans `modules/php/constants.inc.php`.
- Les donnees statiques des cartes et du materiel sont dans `modules/php/cards-*.inc.php` et `modules/php/material.inc.php`; le schema des donnees persistantes est dans `dbmodel.sql`.
- Le client est en TypeScript dans `src/`; l'entree `src/game.ts` est compilee par Rollup vers `modules/js/Game.js`.
- Les styles sont en Sass dans `src/`, avec `src/game.scss` comme entree, et sont compiles vers `starwarsdeckbuilding.css`.
- Les definitions BGA TypeScript sont disponibles dans `bga-framework.d.ts` et `src/types/bga-framework.d.ts`; les types propres au jeu sont dans `src/types/game.d.ts`.

## Principes de modification

- Conserver les conventions et l'architecture existantes; privilegier une modification locale plutot qu'une refonte.
- Ne pas modifier manuellement les fichiers generes `modules/js/Game.js`, `starwarsdeckbuilding.css` et sa source map : modifier les sources dans `src/`, puis compiler.
- Ne pas annuler ni reformater des changements existants sans lien avec la demande.
- Employer les constantes existantes de `modules/php/constants.inc.php` plutot que des valeurs magiques.
- Conserver les messages visibles par les joueurs traduisibles avec `clienttranslate` en PHP et `_()` en TypeScript.

## Backend BGA et etats

- Les etats sont des classes qui etendent `Bga\GameFramework\States\GameState`, definissent un `StateType` (`ACTIVE_PLAYER`, `MULTIPLE_ACTIVE_PLAYER` ou `GAME`) et utilisent les constantes d'etat de `modules/php/constants.inc.php`.
- Toute action joueur est une methode de l'etat concerne annotee `#[PossibleAction]`; elle valide ses parametres et les droits du joueur cote serveur avant toute mutation.
- Les transitions sont retournees par les methodes d'action ou de resolution sous forme de classes d'etat, et les noms des etats doivent rester coherents entre leurs constructeurs PHP et les enregistrements `this.states.register(...)` du client.
- Utiliser `getArgs()` pour exposer les donnees necessaires a l'etat, `onEnteringState()` pour son initialisation serveur et `zombie()` lorsque le comportement de remplacement est necessaire.
- Respecter strictement les droits du joueur actif ou des joueurs actifs, y compris dans les actions AJAX et les etats `MULTIPLE_ACTIVE_PLAYER`.
- Faire porter les regles et les validations par le serveur; le client ne fournit que les interactions, apercus et animations.
- Emettre les notifications BGA necessaires apres une mutation afin que tous les clients restent coherents.
- Pour les changements de donnees persistantes, mettre a jour `dbmodel.sql` si necessaire et employer les repositories, contextes, compteurs, globals et decks BGA existants plutot que du SQL disperse.

## Client TypeScript et Sass

- Centraliser le point d'entree client dans la classe `Game` de `src/game.ts`; enregistrer les gestionnaires avec `this.states.register(...)` et placer les comportements specialises dans `src/states/`.
- Garder les handlers de `src/states/` synchronises avec les noms et arguments des etats PHP; reutiliser `BaseState` et les interfaces de `src/types/game.d.ts`.
- Utiliser les card managers, player tables, table center, stocks, tooltip manager et utilitaires existants avant de creer une nouvelle abstraction UI.
- Gerer les notifications dans `src/notification-manager.ts` et maintenir les animations et les stocks coherents avec les mutations serveur.
- Preserver la compatibilite des navigateurs pris en charge par BGA et les APIs BGA deja utilisees dans le projet.
- Ajouter les styles dans le module Sass le plus proche de leur composant, puis les importer dans la chaine Sass existante.

## Validation

- Apres un changement TypeScript, executer `npm run build:ts`.
- Apres un changement Sass, executer `npm run build:scss`.
- Pour un changement PHP d'etat ou d'action, verifier au minimum la classe `GameState`, le `StateType`, l'attribut `#[PossibleAction]`, les validations, les classes de transition, les arguments, le handler client et les notifications correspondants.
- Pour une verification complete des sorties frontend, executer `npm run build`.
- Les commandes de surveillance disponibles sont `npm run watch:ts` et `npm run watch:scss`.
- Signaler explicitement les validations non executables localement, notamment les tests necessitant une table BGA Studio.