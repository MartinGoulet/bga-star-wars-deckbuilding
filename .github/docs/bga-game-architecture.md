# Architecture d'un jeu BGA PHP/TypeScript

Ce document définit une architecture réutilisable pour un jeu BoardGameArena construit avec le framework BGA v2, un backend PHP et un frontend TypeScript. Il décrit les frontières entre les couches, les contrats de synchronisation et le processus d'ajout d'une mécanique de jeu.

L'objectif est de garder une source de vérité côté serveur tout en fournissant au client les données publiques, les états d'interface et les notifications nécessaires à une expérience fluide.

## 1. Vue d'ensemble

```text
				 Action HTTP / état BGA
						   |
						   v
				   +----------------+
				   |  Game (PHP)    |
				   | composition    |
				   +-------+--------+
						   |
			 +-------------+-------------+
			 v                           v
	  +--------------+             +--------------+
	  | GameState    |             | Service      |
	  | transitions  |             | règles       |
	  +------+-------+             +------+-------+
			 |                            |
			 |                            v
			 |                     +--------------+
			 |                     | Repository   |
			 |                     | SQL uniquement |
			 |                     +------+-------+
			 |                            |
			 +-------------+--------------+
						   v
					Base de données BGA

  Backend -- gamedatas / notifications --> Frontend TypeScript
```

### Responsabilités principales

| Couche | Responsabilité | Ne doit pas faire |
|---|---|---|
| `Game` PHP | Composer les dépendances, initialiser la partie, exposer les données publiques | Porter toute la logique métier |
| `GameState` PHP | Orchestrer un moment du jeu, déclarer les actions et les transitions | Devenir un dépôt SQL ou une seconde interface utilisateur |
| `Service` PHP | Appliquer les règles métier, coordonner les repositories et notifier | Rendre directement du HTML ou lire la base avec du SQL dispersé |
| `Repository` PHP | Lire et écrire une agrégat dans la base | Décider si une action est autorisée selon les règles |
| `Instance` PHP | Représenter une entité ou un résultat typé | Contenir des effets de bord cachés |
| Frontend TypeScript | Afficher l'état public, capturer les actions, jouer les animations | Être l'autorité pour valider une règle |
| `NotificationManager` | Transformer les notifications serveur en mises à jour visuelles | Modifier la logique du jeu |

Le serveur est toujours l'autorité. Le client peut désactiver un bouton pour guider l'utilisateur, mais chaque action doit être validée dans l'état PHP correspondant.

## 2. Structure recommandée

```text
project/
├── dbmodel.sql                 # Tables propres au jeu
├── gameinfos.jsonc             # Métadonnées, joueurs, couleurs
├── gameoptions.jsonc           # Options de partie
├── gamepreferences.jsonc       # Préférences utilisateur
├── material.csv                # Données éditoriales si nécessaire
├── package.json
├── rollup.config.mjs
├── tsconfig.json
├── modules/
│   ├── php/
│   │   ├── Game.php            # Point d'entrée et composition root
│   │   ├── constants.inc.php   # IDs d'états et constantes partagées
│   │   ├── material.inc.php    # Données chargées par le backend
│   │   ├── Entity/
│   │   │   ├── EntityInstance.php
│   │   │   ├── EntityRepository.php
│   │   │   └── EntityService.php
│   │   └── States/
│   │       ├── Setup.php
│   │       ├── Phase01/
│   │       ├── Phase02/
│   │       └── EndScore.php
│   └── js/                     # Sorties compilées et chargées par BGA
└── src/
	├── ts/
	│   ├── Game.ts             # Bootstrap frontend
	│   ├── types.d.ts          # Contrats gamedatas et notifications
	│   ├── Managers/
	│   ├── states/
	│   └── components/
	└── scss/
```

Les noms `Entity`, `Phase01` et `Phase02` sont des placeholders. Ils doivent être remplacés par le vocabulaire du nouveau jeu, sans créer un dossier par règle si une règle peut rester dans un service existant.

## 3. Backend PHP

### `Game` comme composition root

Le constructeur de `Game` instancie les repositories, puis les services qui en dépendent. Les compteurs BGA et les compteurs de table sont également créés à cet endroit.

```php
public function __construct()
{
	parent::__construct();

	$this->entityRepository = new EntityRepository($this);
	$this->entityService = new EntityService($this, $this->entityRepository);

	$this->playerResource = $this->counterFactory->createPlayerCounter('resource');
	$this->roundCounter = $this->counterFactory->createTableCounter('round');
}
```

Le code réel doit conserver `declare(strict_types=1)` et le namespace du jeu. Les services et repositories sont exposés comme propriétés typées afin que les états puissent les utiliser via `$this->game->entityService`.

### Repository : persistance uniquement

Un repository encapsule les requêtes SQL d'un agrégat :

- utiliser les préfixes et conventions de tables attendus par BGA ;
- convertir les lignes SQL en `Instance` lorsque cela apporte une valeur ;
- fournir des méthodes nommées selon l'intention (`findById`, `getByPlayer`, `save`, `delete`) ;
- ne jamais envoyer de notification et ne jamais décider si une action respecte les règles.

Un repository ne doit pas appeler un autre service pour calculer une récompense, une permission ou une transition.

### Service : règles et coordination

Un service regroupe les opérations métier autour d'une entité ou d'un sous-système. Il peut :

- vérifier des préconditions métier ;
- lire plusieurs repositories ;
- modifier les compteurs BGA ;
- créer ou mettre à jour les données ;
- envoyer une notification décrivant le changement public.

Les services reçoivent `Game` et leurs repositories nécessaires par injection. Une méthode de service doit produire un résultat exploitable par l'état ou lever une exception métier claire lorsque l'action est invalide.

### Compteurs et globals

Utiliser les outils du framework selon la portée de la donnée :

- `PlayerCounter` pour une quantité numérique par joueur ;
- `TableCounter` pour une quantité numérique globale à la table ;
- `globals` pour une donnée transversale qui ne correspond pas à un compteur ou qui nécessite une structure ;
- une table dédiée pour une collection, un historique ou une donnée nécessitant des requêtes.

Les globals doivent avoir des clés explicites, une valeur initiale documentée et une durée de vie définie. Éviter d'utiliser une global comme cache permanent d'une donnée déjà stockée en base.

### `getAllDatas()` et confidentialité

`getAllDatas()` reconstruit l'état visible après le chargement initial ou un rafraîchissement. Chaque champ doit être classé :

- public pour tous les joueurs ;
- privé pour le joueur courant ;
- absent si le client n'en a pas besoin.

Les informations privées doivent être placées dans la structure privée prévue par BGA ou filtrées par joueur. Ne jamais retourner à tous les joueurs la main d'un adversaire, un choix secret ou une information cachée uniquement parce que le frontend en faciliterait le rendu.

## 4. Machine à états BGA

### Déclaration

Centraliser les identifiants numériques des états dans `constants.inc.php`. Chaque état PHP étend `GameState`, déclare son type BGA et porte une description traduisible.

```php
class SelectAction extends GameState
{
	public function __construct(protected Game $game)
	{
		parent::__construct(
			$game,
			id: ST_SELECT_ACTION,
			type: StateType::ACTIVE_PLAYER,
			description: clienttranslate('${actplayer} must choose an action'),
			descriptionMyTurn: clienttranslate('${you} must choose an action'),
		);
	}
}
```

Choisir un type en fonction du contrôle de tour :

- `GAME` : traitement automatique ou transition sans action utilisateur ;
- `ACTIVE_PLAYER` : un seul joueur agit ;
- `MULTIPLE_ACTIVE_PLAYER` : plusieurs joueurs peuvent agir indépendamment ;
- `PRIVATE` : choix privé rattaché à un joueur dans un flux multi-actif.

### Cycle de vie d'un état

Les méthodes usuelles sont :

- `onEnteringState(array $args)` : appliquer l'effet d'entrée et retourner l'état suivant lorsque le traitement est automatique ;
- `getArgs()` : fournir au client les données de l'état, sans exposer de secret ;
- une méthode `#[PossibleAction]` par action utilisateur ;
- `zombie(int $playerId)` : choix automatique et cohérent lorsqu'un joueur devient zombie.

Une action reçoit l'identifiant du joueur courant ou le paramètre attendu par la convention du framework. Elle valide ses entrées côté serveur, appelle un service, puis utilise `setPlayerNonMultiactive`, `setPlayersMultiactive` ou une transition explicite.

```php
#[PossibleAction]
public function actChooseOption(int $option, int $currentPlayerId): void
{
	$this->game->choiceService->choose($currentPlayerId, $option);
	$this->gamestate->setPlayerNonMultiactive($currentPlayerId, ST_NEXT_STATE);
}
```

Pour une action simultanée qui demande ensuite un détail à chaque joueur, utiliser un état multi-actif public puis un état privé par joueur. Le sous-état privé ne doit recevoir que les choix et données du joueur concerné.

### Transitions

Une transition doit être déterministe et lisible. L'état qui orchestre appelle le service qui applique la règle, puis retourne la classe de l'état suivant ou configure les joueurs actifs selon le type BGA. Éviter les transitions cachées dans un repository ou dans une notification.

Les états de début et de fin de phase sont de bons endroits pour :

- initialiser les données de la phase ;
- résoudre les effets automatiques ;
- nettoyer les globals temporaires ;
- produire une notification de changement de phase ;
- calculer l'état suivant.

## 5. Frontend TypeScript

### Bootstrap `Game`

`Game.ts` est le point d'entrée du client. Son rôle est de :

1. conserver `gamedatas` ;
2. créer les composants visuels ;
3. initialiser les managers ;
4. enregistrer les états frontend dans `bga.states` ;
5. installer les notifications et les tooltips ;
6. restaurer l'interface après un chargement ou un rafraîchissement.

Les types de `gamedatas`, joueurs, entités et notifications sont définis dans `types.d.ts` ou dans `src/ts/types/`. Un champ ajouté au backend doit être ajouté au contrat TypeScript dans le même changement.

### Composants et managers

Séparer les responsabilités visuelles :

- composants de surface : panneau joueur, plateau, zone centrale, réserve ;
- managers : rendu et interactions d'une entité répétée, comme des cartes, jetons ou dés ;
- `NotificationManager` : application des changements diffusés par le serveur ;
- états frontend : boutons actifs, zones cliquables et nettoyage lors de la sortie d'état.

Un manager ne doit pas décider qu'une action est valide. Il peut calculer un affichage, poser un écouteur et appeler une action BGA, mais le serveur tranche.

### États frontend

Chaque état PHP qui demande une interaction possède un état frontend enregistré sous le même nom fonctionnel. Il implémente le contrat `StateHandler<T>` :

```typescript
interface StateHandler<T> {
   onEnteringState(args: T, isCurrentPlayerActive: boolean): void;
   onLeavingState(isCurrentPlayerActive: boolean): void;
}
```

`onEnteringState` affiche les contrôles correspondant à `args`. `onLeavingState` supprime les écouteurs, classes et boutons temporaires. Le client ne doit pas supposer qu'un état est encore actif après une notification ou un rafraîchissement.

## 6. Notifications et synchronisation

Le serveur utilise les notifications BGA pour communiquer les changements publics et déclencher les animations :

```php
$this->notify->all('onEntityUpdated', clienttranslate('${player_name} updates an entity'), [
	'player_id' => $playerId,
	'entity' => $entity,
]);
```

Le client associe automatiquement `onEntityUpdated` à `notif_onEntityUpdated` dans `NotificationManager` lorsque le système `setupPromiseNotifications` est utilisé :

```typescript
private async notif_onEntityUpdated(args: EntityUpdatedArgs): Promise<void> {
   this.game.entityManager.update(args.entity);
   await this.game.animationManager.play(...);
}
```

Règles de contrat :

- le nom de notification et les clés de données sont définis une seule fois par changement ;
- les données doivent être sérialisables et limitées à ce que les destinataires peuvent voir ;
- les notifications décrivent un changement déjà validé côté serveur ;
- une animation doit terminer ou échouer proprement avant de laisser la file de notifications continuer ;
- `getAllDatas()` reste la source de reconstruction après F5, les notifications ne doivent pas être nécessaires pour obtenir un état initial.

Utiliser une notification globale pour un changement public et la variante privée BGA pour une information visible par un seul joueur. Ne pas envoyer une donnée secrète dans une notification globale puis compter sur le CSS pour la masquer.

## 7. Données, assets et build

### Base de données

`dbmodel.sql` décrit uniquement les tables propres au jeu. Les tables standard BGA (`player`, `global`, `stats`, `gamelog`) sont gérées par le framework. Pour chaque table :

- définir une clé primaire et les types adaptés ;
- ajouter des index sur les colonnes de recherche fréquente ;
- distinguer l'identifiant du propriétaire, la localisation et l'état ;
- prévoir la migration dans `upgradeTableDb()` après publication si le schéma évolue.

Initialiser les collections, compteurs et données de départ dans `setupNewGame()`, en gardant la création de données et la logique de résolution séparées.

### Matériel et métadonnées

Utiliser `material.inc.php` ou un fichier de données adapté pour les valeurs statiques du jeu. Les options de partie doivent être définies dans `gameoptions.jsonc` et être lisibles depuis le backend comme depuis `gamedatas`. Les couleurs, limites de joueurs, préférences et traductions doivent rester cohérentes avec `gameinfos.jsonc` et `gamepreferences.jsonc`.

### Frontend et sorties

Les sources TypeScript et SCSS vivent dans `src/`. Rollup compile l'entrée TypeScript vers `modules/js/`; Sass compile la feuille principale vers le CSS chargé par BGA. Les fichiers générés ne sont pas le lieu des modifications manuelles.

Commandes de référence :

```bash
npm install
npm run build:ts
npm run build:scss
npm run build
npm run watch:ts
npm run watch:scss
```

## 8. Ajouter une nouvelle mécanique

Pour chaque mécanique qui traverse plusieurs couches, suivre cet ordre :

1. Définir le vocabulaire et les règles : acteurs, préconditions, effets, informations publiques et privées.
2. Choisir la persistance : compteur, global, table ou deck BGA.
3. Ajouter l'`Instance` et les méthodes du `Repository` nécessaires.
4. Implémenter l'opération métier dans un `Service` testable et réutilisable.
5. Ajouter les constantes d'état et les états PHP nécessaires.
6. Déclarer les actions `#[PossibleAction]`, valider leurs paramètres et prévoir le comportement zombie.
7. Définir les données de `getArgs()` et mettre à jour `getAllDatas()` si l'état doit survivre à un rafraîchissement.
8. Définir les notifications publiques ou privées, leurs messages traduisibles et leurs payloads.
9. Ajouter les types TypeScript correspondants.
10. Ajouter ou modifier le manager visuel et l'état frontend.
11. Ajouter les animations et le nettoyage dans `onLeavingState`.
12. Mettre à jour les styles, assets, traductions et données statiques.
13. Tester les règles côté PHP, le rafraîchissement, les joueurs multiples, le joueur zombie et les options concernées.
14. Compiler et inspecter les erreurs TypeScript, PHP et SCSS avant l'intégration.

## 9. Choix d'architecture et anti-patterns

### À privilégier

- une règle métier nommée dans un service plutôt que dupliquée dans plusieurs états ;
- des états courts qui orchestrent plutôt qu'un état monolithique ;
- des payloads de notification minimaux et typés ;
- une reconstruction complète et sûre via `getAllDatas()` ;
- des données statiques séparées de la logique ;
- des méthodes de repository faciles à vérifier avec une requête SQL isolée.

### À éviter

- écrire du SQL dans `GameState`, `Game.ts` ou un manager frontend ;
- faire dépendre une décision de règle d'un élément DOM ou d'un bouton activé ;
- utiliser `getAllDatas()` pour exposer des informations privées ;
- appeler un service depuis un repository pour obtenir un effet métier ;
- envoyer une notification avant la transaction ou la mutation validée ;
- faire porter à une notification la reconstruction complète de la partie ;
- mélanger les identifiants numériques d'état et les règles dans plusieurs fichiers ;
- modifier les sorties compilées à la main ;
- laisser les interfaces TypeScript diverger silencieusement du payload PHP.

## 10. Checklist avant première partie

- [ ] Le jeu démarre avec le nombre minimal de joueurs.
- [ ] `setupNewGame()` crée toutes les données et compteurs attendus.
- [ ] Un rafraîchissement à chaque état reconstruit l'interface correctement.
- [ ] Les données privées ne sont visibles que par leur destinataire.
- [ ] Chaque action est validée côté serveur et possède une résolution zombie.
- [ ] Les transitions automatiques ne dépendent pas du frontend.
- [ ] Les notifications ont un payload documenté et un handler TypeScript.
- [ ] Les animations ne bloquent pas définitivement la file de notifications.
- [ ] Les options activées et désactivées sont testées.
- [ ] Le score, la progression et la fin de partie sont déterministes.
- [ ] Les changements de schéma disposent d'une stratégie de migration après publication.
- [ ] `npm run build` passe sans erreur.
- [ ] Les fichiers PHP respectent `strict_types`, namespaces et conventions du projet.
