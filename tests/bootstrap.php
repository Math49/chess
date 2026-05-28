<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Contract/Renderable.php';
require_once __DIR__ . '/../src/Enum/PieceColor.php';
require_once __DIR__ . '/../src/Enum/PieceType.php';
require_once __DIR__ . '/../src/Position.php';
require_once __DIR__ . '/../src/Exception/ChessException.php';
require_once __DIR__ . '/../src/Exception/InvalidMoveException.php';
require_once __DIR__ . '/../src/Exception/NoPieceException.php';
require_once __DIR__ . '/../src/Exception/WrongTurnException.php';
require_once __DIR__ . '/../src/Exception/OccupiedByAllyException.php';
require_once __DIR__ . '/../src/Exception/KingExposedException.php';
require_once __DIR__ . '/../src/Move.php';
require_once __DIR__ . '/../src/Piece/Piece.php';
require_once __DIR__ . '/../src/Piece/King.php';
require_once __DIR__ . '/../src/Piece/Queen.php';
require_once __DIR__ . '/../src/Piece/Rook.php';
require_once __DIR__ . '/../src/Piece/Bishop.php';
require_once __DIR__ . '/../src/Piece/Knight.php';
require_once __DIR__ . '/../src/Piece/Pawn.php';
require_once __DIR__ . '/../src/Board.php';
require_once __DIR__ . '/../src/Factory/PieceFactory.php';
require_once __DIR__ . '/../src/Game.php';
