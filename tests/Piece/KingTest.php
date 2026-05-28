<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class KingTest extends TestCase
{
    private Board $board;

    protected function setUp(): void
    {
        $this->board = new Board();
    }

    public function testDeplacementNormal(): void
    {
        $king = new King(PieceColor::WHITE, new Position(4, 4));
        $this->board->placePiece($king);

        $this->assertTrue($king->canMove($this->board, new Position(3, 4))); // haut
        $this->assertTrue($king->canMove($this->board, new Position(5, 5))); // diagonale
        $this->assertTrue($king->canMove($this->board, new Position(4, 3))); // gauche
    }

    public function testDeplacementInterditPlusDUnCase(): void
    {
        $king = new King(PieceColor::WHITE, new Position(4, 4));
        $this->board->placePiece($king);

        $this->assertFalse($king->canMove($this->board, new Position(4, 6)));
        $this->assertFalse($king->canMove($this->board, new Position(2, 4)));
    }

    public function testRoquePossible(): void
    {
        // Roi en e1 (row=7,col=4), tour en h1 (row=7,col=7), chemin libre
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $rook = new Rook(PieceColor::WHITE, new Position(7, 7));
        $this->board->placePiece($king);
        $this->board->placePiece($rook);

        // Roque côté roi : roi se déplace en g1 (row=7,col=6)
        $this->assertTrue($king->canMove($this->board, new Position(7, 6)));
    }

    public function testRoqueImpossibleSiRoiABouge(): void
    {
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $rook = new Rook(PieceColor::WHITE, new Position(7, 7));
        $this->board->placePiece($king);
        $this->board->placePiece($rook);
        $king->markMoved();

        $this->assertFalse($king->canMove($this->board, new Position(7, 6)));
    }

    public function testRoqueImpossibleSiTourABouge(): void
    {
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $rook = new Rook(PieceColor::WHITE, new Position(7, 7));
        $this->board->placePiece($king);
        $this->board->placePiece($rook);
        $rook->markMoved();

        $this->assertFalse($king->canMove($this->board, new Position(7, 6)));
    }

    public function testRoqueImpossibleSiCheminBloque(): void
    {
        $king   = new King(PieceColor::WHITE, new Position(7, 4));
        $rook   = new Rook(PieceColor::WHITE, new Position(7, 7));
        $bishop = new Bishop(PieceColor::WHITE, new Position(7, 5)); // bloque f1
        $this->board->placePiece($king);
        $this->board->placePiece($rook);
        $this->board->placePiece($bishop);

        $this->assertFalse($king->canMove($this->board, new Position(7, 6)));
    }
}
