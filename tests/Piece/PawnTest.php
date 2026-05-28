<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PawnTest extends TestCase
{
    private Board $board;

    protected function setUp(): void
    {
        $this->board = new Board();
    }

    public function testAvanceUneCaseBlancVersBas(): void
    {
        $pawn = new Pawn(PieceColor::WHITE, new Position(6, 4));
        $this->board->placePiece($pawn);

        $this->assertTrue($pawn->canMove($this->board, new Position(5, 4)));
    }

    public function testAvanceDeuxCasesDepuisLaDepartBlanche(): void
    {
        $pawn = new Pawn(PieceColor::WHITE, new Position(6, 4));
        $this->board->placePiece($pawn);

        $this->assertTrue($pawn->canMove($this->board, new Position(4, 4)));
    }

    public function testAvanceDeuxCasesInterditHorsDepart(): void
    {
        $pawn = new Pawn(PieceColor::WHITE, new Position(4, 4));
        $this->board->placePiece($pawn);

        $this->assertFalse($pawn->canMove($this->board, new Position(2, 4)));
    }

    public function testAvanceBloqueeParUnePiece(): void
    {
        $pawn    = new Pawn(PieceColor::WHITE, new Position(6, 4));
        $blocker = new Pawn(PieceColor::BLACK, new Position(5, 4));
        $this->board->placePiece($pawn);
        $this->board->placePiece($blocker);

        $this->assertFalse($pawn->canMove($this->board, new Position(5, 4)));
    }

    public function testPriseDiagonale(): void
    {
        $pawn  = new Pawn(PieceColor::WHITE, new Position(4, 4));
        $enemy = new Pawn(PieceColor::BLACK, new Position(3, 5));
        $this->board->placePiece($pawn);
        $this->board->placePiece($enemy);

        $this->assertTrue($pawn->canMove($this->board, new Position(3, 5)));
    }

    public function testPriseDiagonaleInterditeSiVide(): void
    {
        $pawn = new Pawn(PieceColor::WHITE, new Position(4, 4));
        $this->board->placePiece($pawn);

        $this->assertFalse($pawn->canMove($this->board, new Position(3, 5)));
    }

    public function testPriseEnPassant(): void
    {
        // Pion blanc en e5 (row=3,col=4), cible en passant d6 (row=2,col=3)
        $pawn = new Pawn(PieceColor::WHITE, new Position(3, 4));
        $this->board->placePiece($pawn);
        $this->board->setEnPassantTarget(new Position(2, 3));

        $this->assertTrue($pawn->canMove($this->board, new Position(2, 3)));
    }
}
