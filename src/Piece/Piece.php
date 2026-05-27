<?php

declare(strict_types=1);

abstract class Piece implements Renderable
{
    protected PieceColor $color;
    protected Position $position;
    protected PieceType $type;

    public function __construct(PieceColor $color, Position $position)
    {
        $this->color = $color;
        $this->position = $position;
    }

    public function getColor(): PieceColor
    {
        return $this->color;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }

    public function getType(): PieceType
    {
        return $this->type;
    }

    public function render(): string
    {
        if ($this->color === PieceColor::WHITE) {
            return match ($this->type) {
                PieceType::KING   => '♔',
                PieceType::QUEEN  => '♕',
                PieceType::ROOK   => '♖',
                PieceType::BISHOP => '♗',
                PieceType::KNIGHT => '♘',
                PieceType::PAWN   => '♙',
            };
        }

        return match ($this->type) {
            PieceType::KING   => '♚',
            PieceType::QUEEN  => '♛',
            PieceType::ROOK   => '♜',
            PieceType::BISHOP => '♝',
            PieceType::KNIGHT => '♞',
            PieceType::PAWN   => '♟',
        };
    }

    public function canMove(Board $board, Position $target): bool
    {
        if ($this->position->equals($target)) {
            return false;
        }

        if (!$this->isValidMovementShape($target)) {
            return false;
        }

        if (!($this instanceof Knight) && !$board->isPathClear($this->position, $target)) {
            return false;
        }

        return true;
    }

    abstract protected function isValidMovementShape(Position $target): bool;

    protected function canCapture(Board $board, Position $target): bool
    {
        $piece = $board->getPieceAt($target);
        return $piece === null || $piece->getColor() !== $this->color;
    }
}
