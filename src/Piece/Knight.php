<?php

declare(strict_types=1);

class Knight extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::KNIGHT;
    }

    protected function isValidMovementShape(Position $target): bool
    {
        $rowDiff = abs($target->getRow() - $this->position->getRow());
        $colDiff = abs($target->getColumn() - $this->position->getColumn());

        return ($rowDiff === 2 && $colDiff === 1) || ($rowDiff === 1 && $colDiff === 2);
    }
}
