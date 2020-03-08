<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Sequence;

interface StateHistory
{
    public function add(State $state): self;

    /**
     * @return Sequence<State>
     */
    public function all(): Sequence;

    /**
     * Remove all states before the given date
     */
    public function keepUp(PointInTime $time): self;
}
