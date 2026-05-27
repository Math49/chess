<?php

declare(strict_types=1);

class Board implements Renderable
{
    private array $pieces = [];

    public function placePiece(Piece $piece): void
    {
        $this->pieces[$piece->getPosition()->toKey()] = $piece;
    }

    public function getPieceAt(Position $position): ?Piece
    {
        return $this->pieces[$position->toKey()] ?? null;
    }

    public function hasPieceAt(Position $position): bool
    {
        return isset($this->pieces[$position->toKey()]);
    }

    public function removePieceAt(Position $position): void
    {
        unset($this->pieces[$position->toKey()]);
    }

    public function movePiece(Position $from, Position $to): void
    {
        $piece = $this->pieces[$from->toKey()];
        unset($this->pieces[$from->toKey()]);
        $piece->setPosition($to);
        $this->pieces[$to->toKey()] = $piece;
    }

    public function isPathClear(Position $from, Position $to): bool
    {
        $rowDiff = $to->getRow() - $from->getRow();
        $colDiff = $to->getColumn() - $from->getColumn();

        $rowStep = $rowDiff === 0 ? 0 : ($rowDiff > 0 ? 1 : -1);
        $colStep = $colDiff === 0 ? 0 : ($colDiff > 0 ? 1 : -1);

        $row = $from->getRow() + $rowStep;
        $col = $from->getColumn() + $colStep;

        while ($row !== $to->getRow() || $col !== $to->getColumn()) {
            if ($this->hasPieceAt(new Position($row, $col))) {
                return false;
            }
            $row += $rowStep;
            $col += $colStep;
        }

        return true;
    }

    public function getPieces(): array
    {
        return array_values($this->pieces);
    }

    public function getKingPosition(PieceColor $color): ?Position
    {
        foreach ($this->pieces as $piece) {
            if ($piece->getColor() === $color && $piece->getType() === PieceType::KING) {
                return $piece->getPosition();
            }
        }
        return null;
    }

    public function render(): string
    {
        $files  = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
        $output = '  ' . implode(' ', $files) . "\n";

        for ($row = 0; $row < 8; $row++) {
            $output .= (8 - $row) . ' ';
            for ($col = 0; $col < 8; $col++) {
                $piece   = $this->getPieceAt(new Position($row, $col));
                $output .= $piece !== null ? $piece->render() : '.';
                if ($col < 7) {
                    $output .= ' ';
                }
            }
            $output .= "\n";
        }

        return $output;
    }
}
