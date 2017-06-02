<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\State\Identity;

use Innmind\Homeostasis\{
    State\Identity,
    TimeContinuum\Format\ISO8601WithMilliseconds
};
use Innmind\TimeContinuum\PointInTimeInterface;

final class PointInTime implements Identity
{
    private $time;

    public function __construct(PointInTimeInterface $time)
    {
        $this->time = $time->format(new ISO8601WithMilliseconds);
    }

    public function __toString(): string
    {
        return $this->time;
    }
}
