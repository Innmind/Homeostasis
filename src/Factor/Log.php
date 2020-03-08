<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Factor;

use Innmind\Homeostasis\{
    Factor,
    Sensor,
    Sensor\Log as LogSensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\LogReader\Reader;
use Innmind\Filesystem\Adapter;
use Innmind\Math\Polynom\Polynom;

final class Log implements Factor
{
    private LogSensor $sensor;
    private string $name;

    public function __construct(
        TimeContinuumInterface $clock,
        Reader $reader,
        Adapter $directory,
        Weight $weight,
        Polynom $health,
        callable $watch,
        string $name
    ) {
        $this->sensor = new LogSensor(
            $clock,
            $reader,
            $directory,
            $weight,
            $health,
            $watch
        );
        $this->name = 'log_'.$name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sensor(): Sensor
    {
        return $this->sensor;
    }
}
