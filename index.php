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
require_once __DIR__ . '/src/Exception/KingExposedException.php';
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

function parseSan(string $san, Game $game): array
{
    $san   = trim($san);
    $san   = rtrim($san, '+# ');
    $color = $game->getCurrentPlayer();
    $board = $game->getBoard();

    if ($san === 'O-O-O' || $san === '0-0-0') {
        $kingPos = $board->getKingPosition($color);
        if ($kingPos === null) {
            throw new InvalidArgumentException("Roi introuvable sur le plateau");
        }
        return [new Move($kingPos, new Position($kingPos->getRow(), $kingPos->getColumn() - 2)), null];
    }

    if ($san === 'O-O' || $san === '0-0') {
        $kingPos = $board->getKingPosition($color);
        if ($kingPos === null) {
            throw new InvalidArgumentException("Roi introuvable sur le plateau");
        }
        return [new Move($kingPos, new Position($kingPos->getRow(), $kingPos->getColumn() + 2)), null];
    }

    $promotion = null;
    if (preg_match('/=([QRBNqrbn])$/', $san, $m)) {
        $promotion = match (strtoupper($m[1])) {
            'Q' => PieceType::QUEEN,
            'R' => PieceType::ROOK,
            'B' => PieceType::BISHOP,
            'N' => PieceType::KNIGHT,
        };
        $san = preg_replace('/=[QRBNqrbn]$/', '', $san);
    }

    $pieceType = PieceType::PAWN;
    if (preg_match('/^([KQRBN])/', $san, $m)) {
        $pieceType = match ($m[1]) {
            'K' => PieceType::KING,
            'Q' => PieceType::QUEEN,
            'R' => PieceType::ROOK,
            'B' => PieceType::BISHOP,
            'N' => PieceType::KNIGHT,
        };
        $san = substr($san, 1);
    }

    $san = str_replace('x', '', $san);

    if (strlen($san) < 2) {
        throw new InvalidArgumentException("Notation invalide");
    }

    $destFile = $san[strlen($san) - 2];
    $destRank = (int) $san[strlen($san) - 1];
    $disambig = substr($san, 0, strlen($san) - 2);

    if (!preg_match('/^[a-h]$/', $destFile) || $destRank < 1 || $destRank > 8) {
        throw new InvalidArgumentException("Case invalide dans la notation");
    }

    $destCol = ord($destFile) - ord('a');
    $destRow = 8 - $destRank;
    $to      = new Position($destRow, $destCol);

    $candidates = [];
    foreach ($board->getPieces() as $piece) {
        if ($piece->getColor() !== $color)     continue;
        if ($piece->getType()  !== $pieceType) continue;
        if (!$game->isMoveLegal($piece, $to))  continue;

        if ($disambig !== '') {
            if (strlen($disambig) === 1 && ctype_alpha($disambig)) {
                if ($piece->getPosition()->getColumn() !== ord($disambig) - ord('a')) continue;
            } elseif (strlen($disambig) === 1 && ctype_digit($disambig)) {
                if ($piece->getPosition()->getRow() !== 8 - (int) $disambig) continue;
            } elseif (strlen($disambig) === 2) {
                $dCol = ord($disambig[0]) - ord('a');
                $dRow = 8 - (int) $disambig[1];
                if ($piece->getPosition()->getColumn() !== $dCol) continue;
                if ($piece->getPosition()->getRow()    !== $dRow) continue;
            }
        }

        $candidates[] = $piece;
    }

    if (count($candidates) === 0) {
        throw new InvalidArgumentException("Aucune pièce ne peut jouer « {$san} »");
    }
    if (count($candidates) > 1) {
        throw new InvalidArgumentException("Coup ambigu, précisez la pièce (ex: Nbd2, R1e3)");
    }

    return [new Move($candidates[0]->getPosition(), $to), $promotion];
}

$game = new Game();
$game->start();

echo "\n";
echo "╔══════════════════════════════╗\n";
echo "║        ECHECS EN PHP         ║\n";
echo "╚══════════════════════════════╝\n";
echo "Notation algébrique : e4  Nf3  O-O  O-O-O  exd5  e8=Q\n";
echo "'quit' pour quitter\n\n";

while (true) {
    $color     = $game->getCurrentPlayer();
    $colorName = $color === PieceColor::WHITE ? 'Blancs' : 'Noirs';

    echo $game->getBoard()->render();

    if ($game->isCheckmate($color)) {
        $winner = $color === PieceColor::WHITE ? 'Noirs' : 'Blancs';
        echo "\n╔══════════════════════════════╗\n";
        echo "║       ECHEC ET MAT !         ║\n";
        echo "║   Les {$winner} ont gagné !   ║\n";
        echo "╚══════════════════════════════╝\n";
        break;
    }

    if ($game->isCheck($color)) {
        echo ">>> ECHEC ! Le roi {$colorName} est en échec <<<\n";
    }

    echo "\nTour des {$colorName} > ";
    $input = fgets(STDIN);

    if ($input === false || strtolower(trim($input)) === 'quit') {
        echo "Partie terminée. À bientôt !\n";
        break;
    }

    $san = trim($input);

    if ($san === '') {
        continue;
    }

    try {
        [$move, $promotion] = parseSan($san, $game);
        $game->play($move, $promotion);
        echo "\nCoup joué : {$san}\n\n";
    } catch (NoPieceException) {
        echo "Erreur : aucune pièce sur cette case.\n\n";
    } catch (WrongTurnException) {
        echo "Erreur : ce n'est pas votre tour.\n\n";
    } catch (OccupiedByAllyException) {
        echo "Erreur : cette case est occupée par l'une de vos pièces.\n\n";
    } catch (KingExposedException) {
        echo "Erreur : ce coup expose votre roi.\n\n";
    } catch (InvalidMoveException $e) {
        echo "Erreur : " . $e->getMessage() . "\n\n";
    } catch (InvalidArgumentException $e) {
        echo "Erreur : " . $e->getMessage() . "\n\n";
    }
}
