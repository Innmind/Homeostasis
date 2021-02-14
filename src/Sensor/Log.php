<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor,
    Sensor\Measure\Weight,
};
use Innmind\TimeContinuum\Clock;
use Innmind\LogReader\{
    Reader,
    Log as LogLine,
};
use Innmind\Filesystem\{
    Adapter,
    File,
    Directory,
};
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number,
    Algebra\Integer,
};

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
        /** @var array{total: int, errors: int} */
        $logs = $this
            ->directory
            ->all()
            ->filter(static function(File $file): bool {
                return !$file instanceof Directory;
            })
            ->reduce(
                ['total' => 0, 'errors' => 0],
                function(array $logs, File $file): array {
                    /** @var array{total: int, errors: int} $logs */

                    return $this->countErrors($logs, $file);
                },
            );

        $percentage = $logs['total'] === 0 ? 0 : $logs['errors'] / $logs['total'];
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
            $this->weight,
        );
    }

    /**
     * @param array{total: int, errors: int} $logs
     *
     * @return array{total: int, errors: int}
     */
    private function countErrors(array $logs, File $file): array
    {
        /** @var array{total: int, errors: int} */
        return ($this->read)($file->content())->reduce(
            $logs,
            function(array $logs, LogLine $line): array {
                /** @var array{total: int, errors: int} $logs */

                return [
                    'total' => $logs['total'] + 1,
                    'errors' => $logs['errors'] + (int) ($this->watch)($line),
                ];
            },
        );
    }
}
