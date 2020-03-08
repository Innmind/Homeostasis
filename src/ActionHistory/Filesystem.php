<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\ActionHistory;

use Innmind\Homeostasis\{
    ActionHistory,
    Action,
    Strategy,
    TimeContinuum\Format\ISO8601WithMilliseconds
};
use Innmind\Filesystem\{
    Adapter,
    File,
    Stream\StringStream
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface
};
use Innmind\Immutable\{
    StreamInterface,
    Set,
    Map
};

final class Filesystem implements ActionHistory
{
    private Adapter $filesystem;
    private TimeContinuumInterface $clock;

    public function __construct(
        Adapter $filesystem,
        TimeContinuumInterface $clock
    ) {
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    public function add(Action $action): ActionHistory
    {
        $this->filesystem->add(
            new File\File(
                $this->name($action->time()),
                new StringStream(json_encode($this->normalize($action)))
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
                new Set(Action::class),
                function(Set $actions, string $name, File $file): Set {
                    return $actions->add(
                        $this->denormalize(
                            json_decode((string) $file->content(), true)
                        )
                    );
                }
            )
            ->sort(static function(Action $a, Action $b): bool {
                return $a->time()->aheadOf($b->time());
            });
    }

    /**
     * {@inheritdoc}
     */
    public function keepUp(PointInTimeInterface $time): ActionHistory
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(string $name, File $file) use ($time): void {
                $actiion = $this->denormalize(
                    json_decode((string) $file->content(), true)
                );

                if ($time->aheadOf($actiion->time())) {
                    $this->filesystem->remove($name);
                }
            });

        return $this;
    }

    private function normalize(Action $action): array
    {
        return [
            'time' => $action->time()->format(new ISO8601WithMilliseconds),
            'strategy' => (string) $action->strategy(),
        ];
    }

    private function denormalize(array $data): Action
    {
        return new Action(
            $this->clock->at($data['time']),
            Strategy::{$data['strategy']}()
        );
    }

    private function name(PointInTimeInterface $time): string
    {
        return md5($time->format(new ISO8601WithMilliseconds));
    }
}
