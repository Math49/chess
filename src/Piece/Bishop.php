<?php

declare(strict_types=1);

class Bishop extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::BISHOP;
    }

    protected function isValidMovementShape(Position $target): bool
    {
        $rowDiff = abs($target->getRow() - $this->position->getRow());
        $colDiff = abs($target->getColumn() - $this->position->getColumn());

        return $rowDiff === $colDiff;
    }
}
