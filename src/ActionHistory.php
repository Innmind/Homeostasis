<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\StreamInterface;

interface ActionHistory
{
    public function add(Action $action): self;

    /**
     * @return StreamInterface<Action>
     */
    public function all(): StreamInterface;

    /**
     * Remove all actions before the given date
     */
    public function keepUp(PointInTimeInterface $time): self;
}
