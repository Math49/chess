# Échecs en PHP

Moteur d'échecs en ligne de commande développé en PHP 8, sans framework ni autoloader.

## Lancer le jeu

```bash
php index.php
```

## Notation

Le jeu accepte la **notation algébrique officielle** (SAN) :

| Exemple | Description |
|---------|-------------|
| `e4` | Avance le pion en e4 |
| `Nf3` | Cavalier en f3 |
| `exd5` | Pion en e prend en d5 |
| `O-O` | Roque côté roi |
| `O-O-O` | Roque côté dame |
| `e8=Q` | Promotion en dame |
| `Nbd2` | Désambiguïsation par colonne |
| `R1e3` | Désambiguïsation par rangée |

Les suffixes `+` et `#` sont acceptés mais ignorés.

## Lancer les tests

```bash
php vendor/bin/phpunit
```

---

## Classes principales

- ✅ `Position`
  - ✅ `__construct()`
  - ✅ `getRow()`
  - ✅ `getColumn()`
  - ✅ `equals()`
  - ✅ `toKey()`
  - ✅ `fromKey()`

- ✅ `Move`
  - ✅ `__construct()`
  - ✅ `getFrom()`
  - ✅ `getTo()`

- ✅ `Board`
  - ✅ `placePiece()`
  - ✅ `getPieceAt()`
  - ✅ `hasPieceAt()`
  - ✅ `removePieceAt()`
  - ✅ `movePiece()`
  - ✅ `isPathClear()`
  - ✅ `getPieces()`
  - ✅ `getKingPosition()`
  - ✅ `render()`

- ✅ `Game`
  - ✅ `__construct()`
  - ✅ `start()`
  - ✅ `getBoard()`
  - ✅ `getCurrentPlayer()`
  - ✅ `play()`
  - ✅ `isCheck()`
  - ✅ `setupPieces()`
  - ✅ `switchPlayer()`

## Pièces

- ✅ `Piece`
  - ✅ `__construct()`
  - ✅ `getColor()`
  - ✅ `getPosition()`
  - ✅ `setPosition()`
  - ✅ `getType()`
  - ✅ `render()`
  - ✅ `canMove()`
  - ✅ `isValidMovementShape()`
  - ✅ `canCapture()`

- ✅ `King`
  - ✅ `isValidMovementShape()`

- ✅ `Queen`
  - ✅ `isValidMovementShape()`

- ✅ `Rook`
  - ✅ `isValidMovementShape()`

- ✅ `Bishop`
  - ✅ `isValidMovementShape()`

- ✅ `Knight`
  - ✅ `isValidMovementShape()`

- ✅ `Pawn`
  - ✅ `isValidMovementShape()`

## Factory

- ✅ `PieceFactory`
  - ✅ `create()`

## Interface / Enums

- ✅ `Renderable`
  - ✅ `render()`

- ✅ `PieceColor`
  - ✅ `WHITE`
  - ✅ `BLACK`
  - ✅ `opposite()`

- ✅ `PieceType`
  - ✅ `KING`
  - ✅ `QUEEN`
  - ✅ `ROOK`
  - ✅ `BISHOP`
  - ✅ `KNIGHT`
  - ✅ `PAWN`

## Exceptions

- ✅ `ChessException`
- ✅ `InvalidMoveException`
- ✅ `NoPieceException`
- ✅ `WrongTurnException`
- ✅ `OccupiedByAllyException`

## Bonus

- ✅ Roque
- ✅ Promotion du pion
- ✅ Prise en passant
- ✅ Interdiction de mettre son propre roi en échec
- ✅ Échec et mat
- ❌ Pat
- ❌ Historique complet des coups
- ✅ Tests automatisés
- ✅ Notation Algébrique
