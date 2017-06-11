<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Immutable\SetInterface;

interface Actuator
{
    /**
     * @param SetInterface<State> $states
     */
    public function dramaticDecrease(SetInterface $states): void;

    /**
     * @param SetInterface<State> $states
     */
    public function decrease(SetInterface $states): void;

    /**
     * @param SetInterface<State> $states
     */
    public function holdSteady(SetInterface $states): void;

    /**
     * @param SetInterface<State> $states
     */
    public function increase(SetInterface $states): void;

    /**
     * @param SetInterface<State> $states
     */
    public function dramaticIncrease(SetInterface $states): void;
}
