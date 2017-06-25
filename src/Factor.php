<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

interface Factor
{
    public function name(): string;
    public function sensor(): Sensor;
}
