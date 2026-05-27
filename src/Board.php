<?php

declare(strict_types=1);

class Board implements Renderable
{
    private array $pieces = [];
    private ?Position $enPassantTarget = null;

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

    public function getEnPassantTarget(): ?Position
    {
        return $this->enPassantTarget;
    }

    public function setEnPassantTarget(?Position $position): void
    {
        $this->enPassantTarget = $position;
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
        $light = "\033[107m\033[30m";
        $reset = "\033[0m";

        $output  = "   a  b  c  d  e  f  g  h\n";
        $output .= "  ┌──┬──┬──┬──┬──┬──┬──┬──┐\n";

        for ($row = 0; $row < 8; $row++) {
            $rank    = 8 - $row;
            $output .= $rank . ' │';
            for ($col = 0; $col < 8; $col++) {
                $isLight = ($row + $col) % 2 === 0;
                $piece   = $this->getPieceAt(new Position($row, $col));
                $symbol  = $piece !== null ? $piece->render() : ' ';

                if ($isLight) {
                    $output .= $light . $symbol . ' ' . $reset . '│';
                } else {
                    $output .= $symbol . ' │';
                }
            }
            $output .= " " . $rank . "\n";
            if ($row < 7) {
                $output .= "  ├──┼──┼──┼──┼──┼──┼──┼──┤\n";
            }
        }

        $output .= "  └──┴──┴──┴──┴──┴──┴──┴──┘\n";
        $output  .= "   a  b  c  d  e  f  g  h\n";

        return $output;
    }
}
