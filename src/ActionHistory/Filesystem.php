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
};
use Innmind\Stream\Readable\Stream;
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\Immutable\{
    Sequence,
    Set,
    Map
};

final class Filesystem implements ActionHistory
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

    public function add(Action $action): ActionHistory
    {
        $this->filesystem->add(
            File\File::named(
                $this->name($action->time()),
                Stream::ofContent(json_encode($this->normalize($action)))
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
            ->mapTo(
                Action::class,
                fn(File $file): Action => $this->denormalize(
                    json_decode($file->content()->toString(), true)
                ),
            )
            ->sort(static function(Action $a, Action $b): bool {
                return $a->time()->aheadOf($b->time());
            });
    }

    /**
     * {@inheritdoc}
     */
    public function keepUp(PointInTime $time): ActionHistory
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(File $file) use ($time): void {
                $action = $this->denormalize(
                    json_decode($file->content()->toString(), true)
                );

                if ($time->aheadOf($action->time())) {
                    $this->filesystem->remove($file->name());
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

    private function name(PointInTime $time): string
    {
        return md5($time->format(new ISO8601WithMilliseconds));
    }
}
