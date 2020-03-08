<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Sequence;

interface ActionHistory
{
    public function add(Action $action): void;

    /**
     * @return Sequence<Action>
     */
    public function all(): Sequence;

    /**
     * Remove all actions before the given date
     */
    public function keepUp(PointInTime $time): void;
}
