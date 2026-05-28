<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    // Crée un Game vide (sans start()) puis place les pièces données
    private function makeGame(array $pieces, PieceColor $turn = PieceColor::WHITE): Game
    {
        $game = new Game();
        foreach ($pieces as $piece) {
            $game->getBoard()->placePiece($piece);
        }
        // Jouer des coups fictifs pour changer le tour si nécessaire
        // Plus simple : on teste uniquement le tour WHITE par défaut
        return $game;
    }

    // -------------------------------------------------------------------------
    // Exceptions de base
    // -------------------------------------------------------------------------

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
        // La tour ne peut pas se déplacer en diagonale
        $this->expectException(InvalidMoveException::class);
        $game->play(new Move(new Position(7, 0), new Position(5, 2)));
    }

    // -------------------------------------------------------------------------
    // Bonus 4 : interdiction d'exposer son roi
    // -------------------------------------------------------------------------

    public function testKingExposedException(): void
    {
        // Roi blanc en e1 (row=7,col=4), tour blanche en e4 (row=4,col=4),
        // tour noire en e8 (row=0,col=4) — si la tour blanche bouge hors de la colonne e,
        // le roi est exposé
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::WHITE, new Position(4, 4)),
            new Rook(PieceColor::BLACK, new Position(0, 4)),
        ]);
        $this->expectException(KingExposedException::class);
        $game->play(new Move(new Position(4, 4), new Position(4, 3))); // e4→d4
    }

    // -------------------------------------------------------------------------
    // Bonus 5 : isCheck et isCheckmate
    // -------------------------------------------------------------------------

    public function testIsCheckDetecteEchec(): void
    {
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Rook(PieceColor::BLACK, new Position(0, 4)), // attaque la colonne e
        ]);
        $this->assertTrue($game->isCheck(PieceColor::WHITE));
        $this->assertFalse($game->isCheck(PieceColor::BLACK));
    }

    public function testIsCheckmateDetecteMat(): void
    {
        // Roi blanc en a1 (row=7,col=0)
        // Dame noire en b3 (row=5,col=1) → couvre a2, b1, b2
        // Tour noire en h1 (row=7,col=7) → attaque le roi sur la rangée 1
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

    // -------------------------------------------------------------------------
    // Bonus 3 : prise en passant
    // -------------------------------------------------------------------------

    public function testPriseEnPassantSupprimeLePion(): void
    {
        // Pion blanc en e5 (row=3,col=4), pion noir en d5 (row=3,col=3)
        // Cible en passant : d6 (row=2,col=3)
        $blackPawn = new Pawn(PieceColor::BLACK, new Position(3, 3));
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Pawn(PieceColor::WHITE, new Position(3, 4)),
            $blackPawn,
        ]);
        $game->getBoard()->setEnPassantTarget(new Position(2, 3));

        $game->play(new Move(new Position(3, 4), new Position(2, 3)));

        // Le pion blanc est arrivé en d6
        $this->assertInstanceOf(Pawn::class, $game->getBoard()->getPieceAt(new Position(2, 3)));
        // Le pion noir en d5 a été supprimé
        $this->assertNull($game->getBoard()->getPieceAt(new Position(3, 3)));
    }

    // -------------------------------------------------------------------------
    // Bonus 1 : roque
    // -------------------------------------------------------------------------

    public function testRoqueDeplaceRoiEtTour(): void
    {
        $king = new King(PieceColor::WHITE, new Position(7, 4));
        $rook = new Rook(PieceColor::WHITE, new Position(7, 7));
        $game = $this->makeGame([$king, $rook]);

        // Roque côté roi : e1→g1
        $game->play(new Move(new Position(7, 4), new Position(7, 6)));

        $this->assertInstanceOf(King::class, $game->getBoard()->getPieceAt(new Position(7, 6)));
        $this->assertInstanceOf(Rook::class, $game->getBoard()->getPieceAt(new Position(7, 5)));
        $this->assertNull($game->getBoard()->getPieceAt(new Position(7, 4)));
        $this->assertNull($game->getBoard()->getPieceAt(new Position(7, 7)));
        $this->assertTrue($king->hasMoved());
        $this->assertTrue($rook->hasMoved());
    }

    // -------------------------------------------------------------------------
    // Bonus 2 : promotion
    // -------------------------------------------------------------------------

    public function testPromotionRemplaceLePion(): void
    {
        // Pion blanc en e7 (row=1,col=4), avance en e8 (row=0,col=4)
        $game = $this->makeGame([
            new King(PieceColor::WHITE, new Position(7, 4)),
            new Pawn(PieceColor::WHITE, new Position(1, 4)),
        ]);
        $game->play(new Move(new Position(1, 4), new Position(0, 4)), PieceType::QUEEN);

        $promoted = $game->getBoard()->getPieceAt(new Position(0, 4));
        $this->assertInstanceOf(Queen::class, $promoted);
        $this->assertSame(PieceColor::WHITE, $promoted->getColor());
    }

    public function testPromotionEnTourParDefautDame(): void
    {
        $game = $this->makeGame([
            new Pawn(PieceColor::WHITE, new Position(1, 0)),
        ]);
        // Sans paramètre → promotion en dame automatique
        $game->play(new Move(new Position(1, 0), new Position(0, 0)));

        $this->assertInstanceOf(Queen::class, $game->getBoard()->getPieceAt(new Position(0, 0)));
    }
}
