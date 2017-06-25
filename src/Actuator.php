<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Immutable\StreamInterface;

interface Actuator
{
    /**
     * @param StreamInterface<State> $states
     */
    public function dramaticDecrease(StreamInterface $states): void;

    /**
     * @param StreamInterface<State> $states
     */
    public function decrease(StreamInterface $states): void;

    /**
     * @param StreamInterface<State> $states
     */
    public function holdSteady(StreamInterface $states): void;

    /**
     * @param StreamInterface<State> $states
     */
    public function increase(StreamInterface $states): void;

    /**
     * @param StreamInterface<State> $states
     */
    public function dramaticIncrease(StreamInterface $states): void;
}
