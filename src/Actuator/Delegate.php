<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\Actuator;
use Innmind\Immutable\StreamInterface;

final class Delegate implements Actuator
{
    private $actuators;

    public function __construct(Actuator ...$actuators)
    {
        $this->actuators = $actuators;
    }

    /**
     * {@inheritdoc}
     */
    public function dramaticDecrease(StreamInterface $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticDecrease($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrease(StreamInterface $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->decrease($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function holdSteady(StreamInterface $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->holdSteady($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function increase(StreamInterface $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->increase($states);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dramaticIncrease(StreamInterface $states): void
    {
        foreach ($this->actuators as $actuator) {
            $actuator->dramaticIncrease($states);
        }
    }
}
