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

    /**
     * {@inheritdoc}
     */
    public function dramaticDecrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticDecrease($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->decrease($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function holdSteady(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->holdSteady($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function increase(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->increase($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dramaticIncrease(Sequence $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticIncrease($states);
        }
    }
}
