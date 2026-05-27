<?php

declare(strict_types=1);

class King extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::KING;
    }

    public function canMove(Board $board, Position $target): bool
    {
        $rowDiff = abs($target->getRow() - $this->position->getRow());
        $colDiff = abs($target->getColumn() - $this->position->getColumn());

        // Roque : le roi se déplace de 2 cases horizontalement
        if ($rowDiff === 0 && $colDiff === 2 && !$this->hasMoved) {
            return $this->canCastle($board, $target);
        }

        return parent::canMove($board, $target);
    }

    private function canCastle(Board $board, Position $target): bool
    {
        $rookCol = $target->getColumn() > $this->position->getColumn() ? 7 : 0;
        $rookPos = new Position($this->position->getRow(), $rookCol);
        $rook    = $board->getPieceAt($rookPos);

        if (!($rook instanceof Rook) || $rook->hasMoved()) {
            return false;
        }

        return $board->isPathClear($this->position, $rookPos);
    }

    protected function isValidMovementShape(Position $target): bool
    {
        $rowDiff = abs($target->getRow() - $this->position->getRow());
        $colDiff = abs($target->getColumn() - $this->position->getColumn());

        return $rowDiff <= 1 && $colDiff <= 1;
    }
}
