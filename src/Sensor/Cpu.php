<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Server\Status\Server;
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number
};

final class Cpu implements Sensor
{
    private $clock;
    private $server;
    private $weight;
    private $health;

    public function __construct(
        TimeContinuumInterface $clock,
        Server $server,
        Weight $weight,
        Polynom $health
    ) {
        $this->clock = $clock;
        $this->server = $server;
        $this->weight = $weight;
        $this->health = $health;
    }

    public function __invoke(): Measure
    {
        $cpu = $this->server->cpu();
        $used = $cpu->user()->toFloat() + $cpu->system()->toFloat();
        $used /= 100;

        return new Measure(
            $this->clock->now(),
            ($this->health)(new Number($used)),
            $this->weight
        );
    }
}
