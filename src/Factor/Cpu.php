<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Factor;

use Innmind\Homeostasis\{
    Factor,
    Sensor,
    Sensor\Cpu as CpuSensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\Clock;
use Innmind\Server\Status\Server;
use Innmind\Math\Polynom\Polynom;

final class Cpu implements Factor
{
    private CpuSensor $sensor;

    public function __construct(
        Clock $clock,
        Server $server,
        Weight $weight,
        Polynom $health
    ) {
        $this->sensor = new CpuSensor($clock, $server, $weight, $health);
    }

    public function name(): string
    {
        return 'cpu';
    }

    public function sensor(): Sensor
    {
        return $this->sensor;
    }
}
