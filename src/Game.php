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

    public function play(Move $move): void
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

        $this->board->movePiece($from, $to);
        $this->switchPlayer();
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
