<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\StateHistory;

use Innmind\Homeostasis\{
    StateHistory,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    TimeContinuum\Format\ISO8601WithMilliseconds,
};
use Innmind\Filesystem\{
    Adapter,
    File,
};
use Innmind\Stream\Readable\Stream;
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    Sequence,
    Set,
    Map,
};

final class Filesystem implements StateHistory
{
    private Adapter $filesystem;
    private Clock $clock;

    public function __construct(Adapter $filesystem, Clock $clock)
    {
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    public function add(State $state): void
    {
        $this->filesystem->add(
            File\File::named(
                $this->name($state->time()),
                Stream::ofContent(json_encode($this->normalize($state))),
            ),
        );
    }

    public function all(): Sequence
    {
        return $this
            ->filesystem
            ->all()
            ->mapTo(
                State::class,
                fn(File $file): State => $this->denormalize($file),
            )
            ->sort(static function(State $a, State $b): int {
                return (int) $a->time()->aheadOf($b->time());
            });
    }

    public function keepUp(PointInTime $time): void
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(File $file) use ($time): void {
                $state = $this->denormalize($file);

                if ($time->aheadOf($state->time())) {
                    $this->filesystem->remove($file->name());
                }
            });
    }

    private function normalize(State $state): array
    {
        return [
            'time' => $state->time()->format(new ISO8601WithMilliseconds),
            'measures' => $state->measures()->reduce(
                [],
                function(array $measures, string $factor, Measure $measure): array {
                    $measures[$factor] = $this->normalizeMeasure($measure);

                    return $measures;
                },
            ),
        ];
    }

    private function denormalize(File $file): State
    {
        /** @var array{time: string, measures: array<string, array{time: string, value: int|float, weight: int|float}>} */
        $data = json_decode($file->content()->toString(), true);

        return new State(
            $this->clock->at($data['time']),
            $this->denormalizeMeasures($data['measures']),
        );
    }

    private function name(PointInTime $time): string
    {
        return \md5($time->format(new ISO8601WithMilliseconds));
    }

    /**
     * @return array{time: string, value: int|float, weight: int|float}
     */
    private function normalizeMeasure(Measure $measure): array
    {
        return [
            'time' => $measure->time()->format(new ISO8601WithMilliseconds),
            'value' => $measure->value()->value(),
            'weight' => $measure->weight()->value()->value(),
        ];
    }

    /**
     * @param array<string, array{time: string, value: int|float, weight: int|float}> $data
     *
     * @return Map<string, Measure>
     */
    private function denormalizeMeasures(array $data): Map
    {
        /** @var Map<string, Measure> */
        $map = Map::of('string', Measure::class);

        foreach ($data as $factor => $measure) {
            $map = ($map)(
                $factor,
                new Measure(
                    $this->clock->at($measure['time']),
                    new Number($measure['value']),
                    new Weight(new Number($measure['weight'])),
                ),
            );
        }

        return $map;
    }
}
