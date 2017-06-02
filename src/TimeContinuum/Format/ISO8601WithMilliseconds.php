<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\TimeContinuum\Format;

use Innmind\TimeContinuum\FormatInterface;

final class ISO8601WithMilliseconds implements FormatInterface
{
    public function __toString(): string
    {
        return 'Y-m-d\TH:i:s.uP';
    }
}
