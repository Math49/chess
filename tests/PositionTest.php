<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testCreationValide(): void
    {
        $pos = new Position(3, 5);
        $this->assertSame(3, $pos->getRow());
        $this->assertSame(5, $pos->getColumn());
    }

    public function testCreationHorsPlateau(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Position(8, 0);
    }

    public function testCreationColonneNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Position(0, -1);
    }

    public function testEquals(): void
    {
        $a = new Position(2, 3);
        $b = new Position(2, 3);
        $c = new Position(2, 4);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToKeyEtFromKey(): void
    {
        $pos = new Position(4, 7);
        $this->assertSame('4:7', $pos->toKey());
        $this->assertTrue($pos->equals(Position::fromKey('4:7')));
    }
}
