<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\ActionHistory;

use Innmind\Homeostasis\{
    ActionHistory\Filesystem,
    ActionHistory,
    Action,
    Strategy,
    TimeContinuum\Format\ISO8601WithMilliseconds
};
use Innmind\Filesystem\{
    Adapter,
    File,
    Name,
};
use Innmind\Stream\Readable\Stream;
use Innmind\TimeContinuum\{
    Clock,
    Earth\Clock as Earth,
};
use Innmind\Immutable\{
    Set,
    Sequence,
};
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ActionHistory::class,
            new Filesystem(
                $this->createMock(Adapter::class),
                $this->createMock(Clock::class)
            )
        );
    }

    public function testAdd()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(Adapter::class),
            $clock = new Earth
        );
        $now = $clock->now();
        $action = new Action(
            $now,
            Strategy::holdSteady()
        );
        $now = $now->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(static function(File $file) use ($now): bool {
                return $file->name()->toString() === \md5($now) &&
                    $file->content()->toString() === \json_encode([
                        'time' => $now,
                        'strategy' => 'holdSteady',
                    ]);
            }));

        $this->assertNull($history->add($action));
    }

    public function testAll()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(Adapter::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        \usleep(100);
        $now2 = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                Set::of(
                    File::class,
                    File\File::named(
                        \md5('foo'),
                        Stream::ofContent(\json_encode([
                            'time' => $now,
                            'strategy' => 'holdSteady',
                        ]))
                    ),
                    File\File::named(
                        \md5('bar'),
                        Stream::ofContent(\json_encode([
                            'time' => $now2,
                            'strategy' => 'increase',
                        ]))
                    ),
                ),
            );

        $actions = $history->all();

        $this->assertInstanceOf(Sequence::class, $actions);
        $this->assertSame(Action::class, (string) $actions->type());
        $this->assertCount(2, $actions);
        $actions = unwrap($actions);
        $action = \current($actions);
        $this->assertInstanceOf(Action::class, $action);
        $now = $clock->at($now);
        $this->assertTrue($action->time()->equals($now));
        $this->assertSame(Strategy::holdSteady(), $action->strategy());
        \next($actions);
        $action = \current($actions);
        $this->assertInstanceOf(Action::class, $action);
        $now2 = $clock->at($now2);
        $this->assertTrue($action->time()->equals($now2));
        $this->assertSame(Strategy::increase(), $action->strategy());
    }

    public function testKeepUp()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(Adapter::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        \sleep(1);
        $mark = $clock->now();
        \sleep(1);
        $now2 = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                Set::of(
                    File::class,
                    File\File::named(
                        \md5('foo'),
                        Stream::ofContent(\json_encode([
                            'time' => $now,
                            'strategy' => 'increase',
                        ]))
                    ),
                    File\File::named(
                        \md5('bar'),
                        Stream::ofContent(\json_encode([
                            'time' => $now2,
                            'strategy' => 'decrease',
                        ]))
                    ),
                ),
        );
        $filesystem
            ->expects($this->once())
            ->method('remove')
            ->with(new Name(\md5('foo')));

        $this->assertNull($history->keepUp($mark));
    }
}
