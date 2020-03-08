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
    Map
};

final class Filesystem implements StateHistory
{
    private Adapter $filesystem;
    private Clock $clock;

    public function __construct(
        Adapter $filesystem,
        Clock $clock
    ) {
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    public function add(State $state): StateHistory
    {
        $this->filesystem->add(
            File\File::named(
                $this->name($state->time()),
                Stream::ofContent(json_encode($this->normalize($state)))
            )
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): Sequence
    {
        return $this
            ->filesystem
            ->all()
            ->reduce(
                Set::of(State::class),
                function(Set $states, File $file): Set {
                    return $states->add(
                        $this->denormalize(
                            json_decode($file->content()->toString(), true)
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
    public function keepUp(PointInTime $time): StateHistory
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(File $file) use ($time): void {
                $state = $this->denormalize(
                    json_decode($file->content()->toString(), true)
                );

                if ($time->aheadOf($state->time())) {
                    $this->filesystem->remove($file->name());
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

    private function name(PointInTime $time): string
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
        $map = Map::of('string', Measure::class);

        foreach ($data as $factor => $measure) {
            $map = ($map)(
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
