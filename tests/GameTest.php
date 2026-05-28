<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private function makeGame(array $pieces): Game
    {
        $game = new Game();
        foreach ($pieces as $piece) {
            $game->getBoard()->placePiece($piece);
        }
        return $game;
    }

    public function testNoPieceException(): void
    {
        $game = new Game();
        $this->expectException(NoPieceException::class);
        $game->play(new Move(new Position(4, 4), new Position(3, 4)));
    }

    public function testWrongTurnException(): void
    {
        $game = $this->makeGame([
            new Pawn(PieceColor::BLACK, new Position(1, 4)),
        ]);
        $this->expectException(WrongTurnException::class);
        $game->play(new Move(new Position(1, 4), new Position(2, 4)));
    }

    public function testOccupiedByAllyException(): void
    {
        $game = $this->makeGame([
            new Rook(PieceColor::WHITE, new Position(7, 0)),
            new Pawn(PieceColor::WHITE, new Position(7, 4)),
        ]);
        $this->expectException(OccupiedByAllyException::class);
        $game->play(new Move(new Position(7, 0), new Position(7, 4)));
    }

    public function testInvalidMoveException(): void
    {
        $game = $this->makeGame([
            new Rook(PieceColor::WHITE, new Position(7, 0)),
        ]);
        $this->expectException(InvalidMoveException::class);
        $game->play(new Move(new Position(7, 0), new Position(5, 2)));
    }

    public function testKingExposedException(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::WHITE, new Position(4, 4)),
            new Rook(PieceColor::BLACK, new Position(0, 4)),
        ]);
        $this->expectException(KingExposedException::class);
        $game->play(new Move(new Position(4, 4), new Position(4, 3)));
    }

    public function testIsMoveLegalRetourneFauxSiExpositionDuRoi(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::WHITE, new Position(4, 4)),
            new Rook(PieceColor::BLACK, new Position(0, 4)),
        ]);
        $rook = $game->getBoard()->getPieceAt(new Position(4, 4));
        $this->assertFalse($game->isMoveLegal($rook, new Position(4, 3)));
        $this->assertTrue($game->isMoveLegal($rook, new Position(3, 4)));
    }

    public function testIsCheckDetecteEchec(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::BLACK, new Position(0, 4)),
        ]);
        $this->assertTrue($game->isCheck(PieceColor::WHITE));
        $this->assertFalse($game->isCheck(PieceColor::BLACK));
    }

    public function testIsCheckmateDetecteMat(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 0)),
            new Queen(PieceColor::BLACK, new Position(5, 1)),
            new Rook(PieceColor::BLACK, new Position(7, 7)),
        ]);
        $this->assertTrue($game->isCheckmate(PieceColor::WHITE));
    }

    public function testIsCheckmateRetourneFauxSiPasEnEchec(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
        ]);
        $this->assertFalse($game->isCheckmate(PieceColor::WHITE));
    }

    public function testPriseEnPassantSupprimeLePion(): void
    {
        $blackPawn = new Pawn(PieceColor::BLACK, new Position(3, 3));
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Pawn(PieceColor::WHITE, new Position(3, 4)),
            $blackPawn,
        ]);
        $game->getBoard()->setEnPassantTarget(new Position(2, 3));

        $game->play(new Move(new Position(3, 4), new Position(2, 3)));

        $this->assertInstanceOf(Pawn::class, $game->getBoard()->getPieceAt(new Position(2, 3)));
        $this->assertNull($game->getBoard()->getPieceAt(new Position(3, 3)));
    }

    public function testEnPassantTargetEstReinitialiseApresUnCoup(): void
    {
        $game = $this->makeGame([
            new Pawn(PieceColor::WHITE, new Position(6, 4)),
            new Pawn(PieceColor::BLACK, new Position(1, 3)),
        ]);

        $game->play(new Move(new Position(6, 4), new Position(4, 4)));
        $this->assertNotNull($game->getBoard()->getEnPassantTarget());

        $game->play(new Move(new Position(1, 3), new Position(2, 3)));
        $this->assertNull($game->getBoard()->getEnPassantTarget());
    }

    public function testRoqueDeplaceRoiEtTour(): void
    {
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $rook = new Rook(PieceColor::WHITE, new Position(7, 7));
        $game = $this->makeGame([$king, $rook]);

        $game->play(new Move(new Position(7, 4), new Position(7, 6)));

        $this->assertInstanceOf(King::class, $game->getBoard()->getPieceAt(new Position(7, 6)));
        $this->assertInstanceOf(Rook::class, $game->getBoard()->getPieceAt(new Position(7, 5)));
        $this->assertNull($game->getBoard()->getPieceAt(new Position(7, 4)));
        $this->assertNull($game->getBoard()->getPieceAt(new Position(7, 7)));
        $this->assertTrue($king->hasMoved());
        $this->assertTrue($rook->hasMoved());
    }

    public function testRoqueImpossibleEnEchec(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::WHITE, new Position(7, 7)),
            new Rook(PieceColor::BLACK, new Position(0, 4)),
        ]);
        $this->expectException(InvalidMoveException::class);
        $game->play(new Move(new Position(7, 4), new Position(7, 6)));
    }

    public function testPromotionRemplaceLePion(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Pawn(PieceColor::WHITE, new Position(1, 4)),
        ]);
        $game->play(new Move(new Position(1, 4), new Position(0, 4)), PieceType::QUEEN);

        $promoted = $game->getBoard()->getPieceAt(new Position(0, 4));
        $this->assertInstanceOf(Queen::class, $promoted);
        $this->assertSame(PieceColor::WHITE, $promoted->getColor());
    }

    public function testPromotionSansPrecisionDonneUneDame(): void
    {
        $game = $this->makeGame([
            new Pawn(PieceColor::WHITE, new Position(1, 0)),
        ]);
        $game->play(new Move(new Position(1, 0), new Position(0, 0)));

        $this->assertInstanceOf(Queen::class, $game->getBoard()->getPieceAt(new Position(0, 0)));
    }
}
