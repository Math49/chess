<?php

declare(strict_types=1);

class PieceFactory
{
    public function create(PieceType $type, PieceColor $color, Position $position): Piece
    {
        return match ($type) {
            PieceType::KING   => new King($color, $position),
            PieceType::QUEEN  => new Queen($color, $position),
            PieceType::ROOK   => new Rook($color, $position),
            PieceType::BISHOP => new Bishop($color, $position),
            PieceType::KNIGHT => new Knight($color, $position),
            PieceType::PAWN   => new Pawn($color, $position),
        };
    }
}
