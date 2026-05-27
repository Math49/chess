<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Contract/Renderable.php';
require_once __DIR__ . '/src/Enum/PieceColor.php';
require_once __DIR__ . '/src/Enum/PieceType.php';
require_once __DIR__ . '/src/Position.php';
require_once __DIR__ . '/src/Exception/ChessException.php';
require_once __DIR__ . '/src/Exception/InvalidMoveException.php';
require_once __DIR__ . '/src/Exception/NoPieceException.php';
require_once __DIR__ . '/src/Exception/WrongTurnException.php';
require_once __DIR__ . '/src/Exception/OccupiedByAllyException.php';
require_once __DIR__ . '/src/Move.php';
require_once __DIR__ . '/src/Piece/Piece.php';
require_once __DIR__ . '/src/Piece/King.php';
require_once __DIR__ . '/src/Piece/Queen.php';
require_once __DIR__ . '/src/Piece/Rook.php';
require_once __DIR__ . '/src/Piece/Bishop.php';
require_once __DIR__ . '/src/Piece/Knight.php';
require_once __DIR__ . '/src/Piece/Pawn.php';
require_once __DIR__ . '/src/Board.php';
require_once __DIR__ . '/src/Factory/PieceFactory.php';
require_once __DIR__ . '/src/Game.php';

function parseSquare(string $square): Position
{
    $square = strtolower(trim($square));
    if (!preg_match('/^[a-h][1-8]$/', $square)) {
        throw new InvalidArgumentException("Notation invalide : \"{$square}\" (attendu ex: e2)");
    }
    $col = ord($square[0]) - ord('a');
    $row = 8 - (int) $square[1];
    return new Position($row, $col);
}

function toSquare(Position $pos): string
{
    $file = chr(ord('a') + $pos->getColumn());
    $rank = 8 - $pos->getRow();
    return $file . $rank;
}

$game = new Game();
$game->start();

echo "\n";
echo "╔══════════════════════════════╗\n";
echo "║        ECHECS EN PHP         ║\n";
echo "╚══════════════════════════════╝\n";
echo "Commandes : saisir le coup en notation algébrique (ex: e2 e4)\n";
echo "           Blancs en Majuscules; Noirs en minuscules\n";
echo "           'quit' pour quitter\n\n";

while (true) {
    $color     = $game->getCurrentPlayer();
    $colorName = $color === PieceColor::WHITE ? 'Blancs' : 'Noirs';

    echo $game->getBoard()->render();

    if ($game->isCheck($color)) {
        echo ">>> ECHEC ! Le roi {$colorName} est en échec <<<\n";
    }

    echo "\nTour des {$colorName} > ";
    $input = fgets(STDIN);

    if ($input === false || strtolower(trim($input)) === 'quit') {
        echo "Partie terminée. A bientôt !\n";
        break;
    }

    $parts = preg_split('/\s+/', trim($input));

    if (count($parts) !== 2) {
        echo "Format invalide. Exemple : e2 e4\n\n";
        continue;
    }

    try {
        $from = parseSquare($parts[0]);
        $to   = parseSquare($parts[1]);
        $game->play(new Move($from, $to));
        echo "\nCoup joué : " . toSquare($from) . " -> " . toSquare($to) . "\n\n";
    } catch (NoPieceException $e) {
        echo "Erreur : aucune pièce sur cette case.\n\n";
    } catch (WrongTurnException $e) {
        echo "Erreur : ce n'est pas votre tour.\n\n";
    } catch (OccupiedByAllyException $e) {
        echo "Erreur : cette case est occupée par l'une de vos pièces.\n\n";
    } catch (InvalidMoveException $e) {
        echo "Erreur : coup invalide pour cette pièce.\n\n";
    } catch (InvalidArgumentException $e) {
        echo "Erreur : " . $e->getMessage() . "\n\n";
    }
}
