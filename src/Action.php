<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Math\Algebra\Integer;

/**
 * Strategy applied at a given time
 */
final class Action
{
    private $time;
    private $strategy;

    public function __construct(
        PointInTimeInterface $time,
        Strategy $strategy
    ) {
        $this->time = $time;
        $this->strategy = $strategy;
    }

    public function time(): PointInTimeInterface
    {
        return $this->time;
    }

    public function strategy(): Strategy
    {
        return $this->strategy;
    }

    public function variation(self $previous): Integer
    {
        return new Integer(
            $this->weight() <=> $previous->weight()
        );
    }

    private function weight(): float
    {
        switch ($this->strategy) {
            case Strategy::dramaticDecrease():
                return 1;

            case Strategy::decrease():
            case Strategy::holdSteady():
            case Strategy::increase():
                return 0.5;

            case Strategy::dramaticIncrease():
                return 0;
        }
    }
}
