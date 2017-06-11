<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\StateRepository;

use Innmind\Homeostasis\{
    StateRepository,
    State,
    State\Identity,
    State\Identity\PointInTime,
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
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    SetInterface,
    Set,
    Map
};

final class Filesystem implements StateRepository
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

    public function add(State $state): StateRepository
    {
        $this->filesystem->add(
            new File(
                $this->name($state->identity()),
                new StringStream(json_encode($this->normalize($state)))
            )
        );

        return $this;
    }

    public function get(Identity $identity): State
    {
        return $this->denormalize(
            json_decode(
                (string) $this
                    ->filesystem
                    ->get($this->name($identity))
                    ->content(),
                true
            )
        );
    }

    public function remove(Identity $identity): StateRepository
    {
        $this->filesystem->remove($this->name($identity));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): SetInterface
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
            );
    }

    private function normalize(State $state): array
    {
        return [
            'identity' => (string) $state->identity(),
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
            new PointInTime($this->clock->at($data['identity'])),
            $this->clock->at($data['time']),
            $this->denormalizeMeasures($data['measures'])
        );
    }

    private function name(Identity $identity): string
    {
        return md5((string) $identity);
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
