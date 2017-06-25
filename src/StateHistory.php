<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\StreamInterface;

interface StateHistory
{
    public function add(State $state): self;

    /**
     * @return StreamInterface<State>
     */
    public function all(): StreamInterface;

    /**
     * Remove all states before the given date
     */
    public function keepUp(PointInTimeInterface $time): self;
}
