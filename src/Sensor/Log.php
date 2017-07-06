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
    Algebra\Number\Number,
    Algebra\Integer
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
        $percentage = $logs->size() === 0 ? 0 : $errors->size() / $logs->size();
        $health = ($this->health)(new Number($percentage));

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
