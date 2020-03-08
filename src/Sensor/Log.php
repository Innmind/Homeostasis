<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\Clock;
use Innmind\LogReader\{
    Reader,
    Log as LogLine
};
use Innmind\Filesystem\{
    Adapter,
    File,
    Directory
};
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number,
    Algebra\Integer
};
use Innmind\Immutable\Sequence;

final class Log implements Sensor
{
    private Clock $clock;
    private Reader $read;
    private Adapter $directory;
    private Weight $weight;
    private Polynom $health;
    /** @var \Closure(LogLine): bool */
    private \Closure $watch;

    /**
     * @param callable(LogLine): bool $watch
     */
    public function __construct(
        Clock $clock,
        Reader $read,
        Adapter $directory,
        Weight $weight,
        Polynom $health,
        callable $watch
    ) {
        $this->clock = $clock;
        $this->read = $read;
        $this->directory = $directory;
        $this->weight = $weight;
        $this->health = $health;
        /** @var \Closure(LogLine): bool */
        $this->watch = \Closure::fromCallable($watch);
    }

    public function __invoke(): Measure
    {
        $logs = $this
            ->directory
            ->all()
            ->filter(static function(File $file): bool {
                return !$file instanceof Directory;
            })
            ->reduce(
                Sequence::of(LogLine::class),
                function(Sequence $logs, File $file): Sequence {
                    return $logs->append(
                        ($this->read)($file->content())
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
