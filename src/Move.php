<?php

declare(strict_types=1);

class Move
{
    private Position $from;
    private Position $to;

    public function __construct(Position $from, Position $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function getFrom(): Position
    {
        return $this->from;
    }

    public function getTo(): Position
    {
        return $this->to;
    }
}
