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
    Algebra\Number\Number,
    Algebra\Integer
};

final class Cpu implements Sensor
{
    private TimeContinuumInterface $clock;
    private Server $server;
    private Weight $weight;
    private Polynom $health;

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
        $health = ($this->health)(new Number($used));

        if ($health->higherThan(new Integer(1))) {
            $health = new Integer(1);
        }

        if ((new Integer(0))->higherThan($health)) {
            $health = new Integer(0);
        }

        return new Measure(
            $this->clock->now(),
            $health,
            $this->weight
        );
    }
}
