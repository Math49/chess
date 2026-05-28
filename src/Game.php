<?php

declare(strict_types=1);

class Game
{
    private Board $board;
    private PieceColor $currentPlayer;
    private PieceFactory $pieceFactory;

    public function __construct()
    {
        $this->board         = new Board();
        $this->currentPlayer = PieceColor::WHITE;
        $this->pieceFactory  = new PieceFactory();
    }

    public function start(): void
    {
        $this->setupPieces();
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getCurrentPlayer(): PieceColor
    {
        return $this->currentPlayer;
    }

    public function play(Move $move, ?PieceType $promotion = null): void
    {
        $from  = $move->getFrom();
        $to    = $move->getTo();
        $piece = $this->board->getPieceAt($from);

        if ($piece === null) {
            throw new NoPieceException("Aucune pièce en {$from->toKey()}");
        }

        if ($piece->getColor() !== $this->currentPlayer) {
            throw new WrongTurnException("Ce n'est pas le tour des {$this->currentPlayer->name}");
        }

        if (!$piece->canMove($this->board, $to)) {
            throw new InvalidMoveException("Déplacement invalide vers {$to->toKey()}");
        }

        $targetPiece = $this->board->getPieceAt($to);
        if ($targetPiece !== null && $targetPiece->getColor() === $piece->getColor()) {
            throw new OccupiedByAllyException("La case {$to->toKey()} est occupée par un allié");
        }

        $isCastling       = $piece instanceof King && abs($to->getColumn() - $from->getColumn()) === 2;
        $isEnPassant      = $piece instanceof Pawn
            && abs($to->getColumn() - $from->getColumn()) === 1
            && !$this->board->hasPieceAt($to);
        $isPawnDoubleStep = $piece instanceof Pawn && abs($to->getRow() - $from->getRow()) === 2;

        if ($isCastling) {
            if ($this->isCheck($piece->getColor())) {
                throw new InvalidMoveException("Impossible de roquer en étant en échec");
            }
            $direction       = $to->getColumn() > $from->getColumn() ? 1 : -1;
            $intermediatePos = new Position($from->getRow(), $from->getColumn() + $direction);
            if ($this->wouldExposeKing($piece, $intermediatePos)) {
                throw new InvalidMoveException("Impossible de roquer en passant par une case attaquée");
            }
        }

        if ($this->wouldExposeKing($piece, $to)) {
            throw new KingExposedException("Ce mouvement expose votre roi");
        }

        $this->board->movePiece($from, $to);
        $piece->markMoved();

        if ($isEnPassant) {
            $this->board->removePieceAt(new Position($from->getRow(), $to->getColumn()));
        }

        if ($isCastling) {
            $rookFromCol = $to->getColumn() > $from->getColumn() ? 7 : 0;
            $rookToCol   = $to->getColumn() > $from->getColumn()
                ? $to->getColumn() - 1
                : $to->getColumn() + 1;
            $rookFrom    = new Position($from->getRow(), $rookFromCol);
            $rook        = $this->board->getPieceAt($rookFrom);
            $this->board->movePiece($rookFrom, new Position($from->getRow(), $rookToCol));
            $rook->markMoved();
        }

        if ($isPawnDoubleStep) {
            $midRow = intdiv($from->getRow() + $to->getRow(), 2);
            $this->board->setEnPassantTarget(new Position($midRow, $from->getColumn()));
        } else {
            $this->board->setEnPassantTarget(null);
        }

        if ($piece instanceof Pawn) {
            $lastRow = $piece->getColor() === PieceColor::WHITE ? 0 : 7;
            if ($to->getRow() === $lastRow) {
                $promotionType = $promotion ?? PieceType::QUEEN;
                $this->board->removePieceAt($to);
                $this->board->placePiece(
                    $this->pieceFactory->create($promotionType, $piece->getColor(), $to)
                );
            }
        }

        $this->switchPlayer();
    }

    public function isMoveLegal(Piece $piece, Position $to): bool
    {
        if (!$piece->canMove($this->board, $to)) {
            return false;
        }

        $targetPiece = $this->board->getPieceAt($to);
        if ($targetPiece !== null && $targetPiece->getColor() === $piece->getColor()) {
            return false;
        }

        return !$this->wouldExposeKing($piece, $to);
    }

    public function isCheck(PieceColor $color): bool
    {
        $kingPosition = $this->board->getKingPosition($color);
        if ($kingPosition === null) {
            return false;
        }

        foreach ($this->board->getPieces() as $piece) {
            if ($piece->getColor() !== $color && $piece->canMove($this->board, $kingPosition)) {
                return true;
            }
        }

        return false;
    }

    public function isCheckmate(PieceColor $color): bool
    {
        if (!$this->isCheck($color)) {
            return false;
        }

        foreach ($this->board->getPieces() as $piece) {
            if ($piece->getColor() !== $color) {
                continue;
            }

            for ($row = 0; $row < 8; $row++) {
                for ($col = 0; $col < 8; $col++) {
                    $target = new Position($row, $col);

                    if (!$piece->canMove($this->board, $target)) {
                        continue;
                    }

                    $targetPiece = $this->board->getPieceAt($target);
                    if ($targetPiece !== null && $targetPiece->getColor() === $color) {
                        continue;
                    }

                    if (!$this->wouldExposeKing($piece, $target)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function wouldExposeKing(Piece $piece, Position $to): bool
    {
        $from          = $piece->getPosition();
        $capturedPiece = $this->board->getPieceAt($to);

        $enPassantCaptured = null;
        if ($piece instanceof Pawn
            && abs($to->getColumn() - $from->getColumn()) === 1
            && $capturedPiece === null
        ) {
            $enPassantPos      = new Position($from->getRow(), $to->getColumn());
            $enPassantCaptured = $this->board->getPieceAt($enPassantPos);
            if ($enPassantCaptured !== null) {
                $this->board->removePieceAt($enPassantPos);
            }
        }

        $this->board->movePiece($from, $to);
        $exposed = $this->isCheck($piece->getColor());
        $this->board->movePiece($to, $from);

        if ($capturedPiece !== null) {
            $this->board->placePiece($capturedPiece);
        }
        if ($enPassantCaptured !== null) {
            $this->board->placePiece($enPassantCaptured);
        }

        return $exposed;
    }

    private function setupPieces(): void
    {
        $backRank = [
            PieceType::ROOK,
            PieceType::KNIGHT,
            PieceType::BISHOP,
            PieceType::QUEEN,
            PieceType::KING,
            PieceType::BISHOP,
            PieceType::KNIGHT,
            PieceType::ROOK,
        ];

        foreach ($backRank as $col => $type) {
            $this->board->placePiece(
                $this->pieceFactory->create($type, PieceColor::BLACK, new Position(0, $col))
            );
            $this->board->placePiece(
                $this->pieceFactory->create($type, PieceColor::WHITE, new Position(7, $col))
            );
        }

        for ($col = 0; $col < 8; $col++) {
            $this->board->placePiece(
                $this->pieceFactory->create(PieceType::PAWN, PieceColor::BLACK, new Position(1, $col))
            );
            $this->board->placePiece(
                $this->pieceFactory->create(PieceType::PAWN, PieceColor::WHITE, new Position(6, $col))
            );
        }
    }

    private function switchPlayer(): void
    {
        $this->currentPlayer = $this->currentPlayer->opposite();
    }
}
