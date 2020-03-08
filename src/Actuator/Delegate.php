<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\Actuator;
use Innmind\Immutable\Sequence;

final class Delegate implements Actuator
{
    /** @var list<Actuator> */
    private array $actuators;

    public function __construct(Actuator ...$actuators)
    {
        $this->actuators = $actuators;
    }

    public function dramaticDecrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticDecrease($states);
        }
    }

    public function decrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->decrease($states);
        }
    }

    public function holdSteady(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->holdSteady($states);
        }
    }

    public function increase(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->increase($states);
        }
    }

    public function dramaticIncrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticIncrease($states);
        }
    }
}
