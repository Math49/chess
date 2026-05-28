<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    private Board $board;

    protected function setUp(): void
    {
        $this->board = new Board();
    }

    public function testPlacerEtRecupererUnePiece(): void
    {
        $rook = new Rook(PieceColor::WHITE, new Position(7, 0));
        $this->board->placePiece($rook);

        $this->assertSame($rook, $this->board->getPieceAt(new Position(7, 0)));
        $this->assertNull($this->board->getPieceAt(new Position(7, 1)));
    }

    public function testHasPieceAt(): void
    {
        $this->board->placePiece(new Pawn(PieceColor::BLACK, new Position(1, 3)));

        $this->assertTrue($this->board->hasPieceAt(new Position(1, 3)));
        $this->assertFalse($this->board->hasPieceAt(new Position(2, 3)));
    }

    public function testRemovePieceAt(): void
    {
        $this->board->placePiece(new Knight(PieceColor::WHITE, new Position(5, 5)));
        $this->board->removePieceAt(new Position(5, 5));

        $this->assertFalse($this->board->hasPieceAt(new Position(5, 5)));
    }

    public function testMovePiece(): void
    {
        $rook = new Rook(PieceColor::WHITE, new Position(7, 0));
        $this->board->placePiece($rook);
        $this->board->movePiece(new Position(7, 0), new Position(7, 4));

        $this->assertFalse($this->board->hasPieceAt(new Position(7, 0)));
        $this->assertSame($rook, $this->board->getPieceAt(new Position(7, 4)));
        $this->assertSame(7, $rook->getPosition()->getRow());
        $this->assertSame(4, $rook->getPosition()->getColumn());
    }

    public function testIsPathClearHorizontal(): void
    {
        $this->assertTrue($this->board->isPathClear(new Position(7, 0), new Position(7, 7)));

        $this->board->placePiece(new Pawn(PieceColor::WHITE, new Position(7, 4)));
        $this->assertFalse($this->board->isPathClear(new Position(7, 0), new Position(7, 7)));
    }

    public function testIsPathClearDiagonale(): void
    {
        $this->assertTrue($this->board->isPathClear(new Position(4, 0), new Position(0, 4)));

        $this->board->placePiece(new Pawn(PieceColor::BLACK, new Position(2, 2)));
        $this->assertFalse($this->board->isPathClear(new Position(4, 0), new Position(0, 4)));
    }

    public function testGetKingPosition(): void
    {
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $this->board->placePiece($king);

        $pos = $this->board->getKingPosition(PieceColor::WHITE);
        $this->assertNotNull($pos);
        $this->assertTrue($pos->equals(new Position(7, 4)));
        $this->assertNull($this->board->getKingPosition(PieceColor::BLACK));
    }

    public function testEnPassantTarget(): void
    {
        $this->assertNull($this->board->getEnPassantTarget());

        $target = new Position(2, 3);
        $this->board->setEnPassantTarget($target);
        $this->assertTrue($target->equals($this->board->getEnPassantTarget()));

        $this->board->setEnPassantTarget(null);
        $this->assertNull($this->board->getEnPassantTarget());
    }
}
