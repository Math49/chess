<?php

declare(strict_types=1);

class Position
{
    private int $row;
    private int $column;

    public function __construct(int $row, int $column)
    {
        if ($row < 0 || $row > 7 || $column < 0 || $column > 7) {
            throw new InvalidArgumentException("Position ({$row}, {$column}) hors plateau (0-7)");
        }
        $this->row = $row;
        $this->column = $column;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function equals(Position $other): bool
    {
        return $this->row === $other->row && $this->column === $other->column;
    }

    public function toKey(): string
    {
        return "{$this->row}:{$this->column}";
    }

    public static function fromKey(string $key): Position
    {
        [$row, $column] = explode(':', $key);
        return new self((int) $row, (int) $column);
    }
}
