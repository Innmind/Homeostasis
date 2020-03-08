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

    public function add(Action $action): void
    {
        $this->filesystem->add(
            File\File::named(
                $this->name($action->time()),
                Stream::ofContent(json_encode($this->normalize($action)))
            )
        );
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
                fn(File $file): Action => $this->denormalize($file),
            )
            ->sort(static function(Action $a, Action $b): int {
                return (int) $a->time()->aheadOf($b->time());
            });
    }

    /**
     * {@inheritdoc}
     */
    public function keepUp(PointInTime $time): void
    {
        $this
            ->filesystem
            ->all()
            ->foreach(function(File $file) use ($time): void {
                $action = $this->denormalize($file);

                if ($time->aheadOf($action->time())) {
                    $this->filesystem->remove($file->name());
                }
            });
    }

    /**
     * @return array{time: string, strategy: string}
     */
    private function normalize(Action $action): array
    {
        return [
            'time' => $action->time()->format(new ISO8601WithMilliseconds),
            'strategy' => $action->strategy()->toString(),
        ];
    }

    private function denormalize(File $file): Action
    {
        /** @var array{time: string, strategy: string} */
        $data = json_decode($file->content()->toString(), true);

        return new Action(
            $this->clock->at($data['time']),
            Strategy::of($data['strategy']),
        );
    }

    private function name(PointInTime $time): string
    {
        return md5($time->format(new ISO8601WithMilliseconds));
    }
}
