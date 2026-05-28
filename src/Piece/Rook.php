<?php

declare(strict_types=1);

class Rook extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::ROOK;
    }

    protected function isValidMovementShape(Position $target): bool
    {
        return $target->getRow() === $this->position->getRow()
            || $target->getColumn() === $this->position->getColumn();
    }
}
