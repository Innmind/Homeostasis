<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\TimeContinuum\Format;

use Innmind\TimeContinuum\Format;

/**
 * @psalm-immutable
 */
final class ISO8601WithMilliseconds implements Format
{
    public function toString(): string
    {
        return 'Y-m-d\TH:i:s.uP';
    }
}
