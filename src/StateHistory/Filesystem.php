<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\StateHistory;

use Innmind\Homeostasis\{
    StateHistory,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    TimeContinuum\Format\ISO8601WithMilliseconds
};
use Innmind\Filesystem\{
    AdapterInterface,
    File,
    FileInterface,
    Stream\StringStream
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    StreamInterface,
    Set,
    Map
};

final class Filesystem implements StateHistory
{
    private $filesystem;
    private $clock;

    public function __construct(
        AdapterInterface $filesystem,
        TimeContinuumInterface $clock
    ) {
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    public function add(State $state): StateHistory
    {
        $this->filesystem->add(
            new File(
                $this->name($state->time()),
                new StringStream(json_encode($this->normalize($state)))
            )
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): StreamInterface
    {
        return $this
            ->filesystem
            ->all()
            ->reduce(
                new Set(State::class),
                function(Set $states, string $name, FileInterface $file): Set {
                    return $states->add(
                        $this->denormalize(
                            json_decode((string) $file->content(), true)
                        )
                    );
                }
            )
            ->sort(static function(State $a, State $b): bool {
                return $a->time()->aheadOf($b->time());
            });
    }

    /**
     * {@inheritdoc}
     */
    public function keepUp(PointInTimeInterface $time): StateHistory
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(string $name, FileInterface $file) use ($time): void {
                $state = $this->denormalize(
                    json_decode((string) $file->content(), true)
                );

                if ($time->aheadOf($state->time())) {
                    $this->filesystem->remove($name);
                }
            });

        return $this;
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
                }
            ),
        ];
    }

    private function denormalize(array $data): State
    {
        return new State(
            $this->clock->at($data['time']),
            $this->denormalizeMeasures($data['measures'])
        );
    }

    private function name(PointInTimeInterface $time): string
    {
        return md5($time->format(new ISO8601WithMilliseconds));
    }

    private function normalizeMeasure(Measure $measure): array
    {
        return [
            'time' => $measure->time()->format(new ISO8601WithMilliseconds),
            'value' => $measure->value()->value(),
            'weight' => $measure->weight()->value()->value(),
        ];
    }

    private function denormalizeMeasures(array $data): Map
    {
        $map = new Map('string', Measure::class);

        foreach ($data as $factor => $measure) {
            $map = $map->put(
                $factor,
                new Measure(
                    $this->clock->at($measure['time']),
                    new Number($measure['value']),
                    new Weight(new Number($measure['weight']))
                )
            );
        }

        return $map;
    }
}
