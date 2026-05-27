<?php

declare(strict_types=1);

class Pawn extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::PAWN;
    }

    // Le pion nécessite le contexte du plateau pour distinguer avance et capture diagonale,
    // donc on surcharge canMove() entièrement plutôt que de déléguer à isValidMovementShape.
    public function canMove(Board $board, Position $target): bool
    {
        if ($this->position->equals($target)) {
            return false;
        }

        $direction = $this->color === PieceColor::WHITE ? -1 : 1;
        $startRow  = $this->color === PieceColor::WHITE ? 6 : 1;
        $rowDiff   = $target->getRow() - $this->position->getRow();
        $colDiff   = abs($target->getColumn() - $this->position->getColumn());

        // Avance simple
        if ($colDiff === 0 && $rowDiff === $direction) {
            return !$board->hasPieceAt($target);
        }

        // Avance double depuis la ligne de départ
        if ($colDiff === 0 && $rowDiff === 2 * $direction && $this->position->getRow() === $startRow) {
            $intermediate = new Position(
                $this->position->getRow() + $direction,
                $this->position->getColumn()
            );
            return !$board->hasPieceAt($intermediate) && !$board->hasPieceAt($target);
        }

        // Capture en diagonale (présence d'une pièce requise ; ally/ennemi vérifié par play())
        if ($colDiff === 1 && $rowDiff === $direction) {
            return $board->hasPieceAt($target);
        }

        return false;
    }

    protected function isValidMovementShape(Position $target): bool
    {
        $direction = $this->color === PieceColor::WHITE ? -1 : 1;
        $startRow  = $this->color === PieceColor::WHITE ? 6 : 1;
        $rowDiff   = $target->getRow() - $this->position->getRow();
        $colDiff   = abs($target->getColumn() - $this->position->getColumn());

        return ($colDiff === 0 && $rowDiff === $direction)
            || ($colDiff === 0 && $rowDiff === 2 * $direction && $this->position->getRow() === $startRow)
            || ($colDiff === 1 && $rowDiff === $direction);
    }
}
