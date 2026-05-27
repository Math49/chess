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

$game = new Game();
$game->start();

echo "=== Plateau initial ===\n";
echo $game->getBoard()->render();

$demo = [
    [[6, 4], [4, 4], 'e2-e4 (pion blanc)'],
    [[1, 4], [3, 4], 'e7-e5 (pion noir)'],
    [[7, 6], [5, 5], 'g1-f3 (cavalier blanc)'],
    [[0, 1], [2, 2], 'b8-c6 (cavalier noir)'],
    [[7, 5], [4, 2], 'f1-c4 (fou blanc)'],
];

foreach ($demo as [$from, $to, $label]) {
    try {
        $game->play(new Move(new Position($from[0], $from[1]), new Position($to[0], $to[1])));
        echo "\n=== Après {$label} ===\n";
        echo $game->getBoard()->render();
    } catch (ChessException $e) {
        echo "\nErreur [{$label}] : " . $e->getMessage() . "\n";
    }
}

echo "\nJoueur courant : " . $game->getCurrentPlayer()->name . "\n";
echo "Blanc en échec  : " . ($game->isCheck(PieceColor::WHITE) ? 'oui' : 'non') . "\n";
echo "Noir en échec   : " . ($game->isCheck(PieceColor::BLACK) ? 'oui' : 'non') . "\n";
