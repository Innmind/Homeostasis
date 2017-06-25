<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\Sensor\Measure;

interface Sensor
{
    public function __invoke(): Measure;
}
