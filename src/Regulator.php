<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

interface Regulator
{
    public function __invoke(): Strategy;
}
