<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Immutable\Sequence;

interface Actuator
{
    /**
     * @param Sequence<State> $states
     */
    public function dramaticDecrease(Sequence $states): void;

    /**
     * @param Sequence<State> $states
     */
    public function decrease(Sequence $states): void;

    /**
     * @param Sequence<State> $states
     */
    public function holdSteady(Sequence $states): void;

    /**
     * @param Sequence<State> $states
     */
    public function increase(Sequence $states): void;

    /**
     * @param Sequence<State> $states
     */
    public function dramaticIncrease(Sequence $states): void;
}
