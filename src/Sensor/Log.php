<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\LogReader\{
    Reader,
    Log as LogLine
};
use Innmind\Filesystem\{
    AdapterInterface,
    FileInterface
};
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number
};
use Innmind\Immutable\Stream;

final class Log implements Sensor
{
    private $clock;
    private $reader;
    private $directory;
    private $weight;
    private $health;
    private $watch;

    public function __construct(
        TimeContinuumInterface $clock,
        Reader $reader,
        AdapterInterface $directory,
        Weight $weight,
        Polynom $health,
        callable $watch
    ) {
        $this->clock = $clock;
        $this->reader = $reader;
        $this->directory = $directory;
        $this->weight = $weight;
        $this->health = $health;
        $this->watch = $watch;
    }

    public function __invoke(): Measure
    {
        $logs = $this
            ->directory
            ->all()
            ->reduce(
                new Stream(LogLine::class),
                function(Stream $logs, string $name, FileInterface $file): Stream {
                    return $logs->append(
                        $this->reader->parse($file)
                    );
                }
            );
        $errors = $logs->filter(function(LogLine $log): bool {
            return ($this->watch)($log);
        });
        $percentage = $errors->size() / $logs->size();

        return new Measure(
            $this->clock->now(),
            ($this->health)(new Number($percentage)),
            $this->weight
        );
    }
}
