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
    Stream\StringStream
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    TimeContinuum\Earth
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    Map,
    StreamInterface
};
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ActionHistory::class,
            new Filesystem(
                $this->createMock(Adapter::class),
                $this->createMock(TimeContinuumInterface::class)
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
            ->with($this->callback(function(File $file) use ($now): bool {
                return (string) $file->name() === md5($now) &&
                    (string) $file->content() === json_encode([
                        'time' => $now,
                        'strategy' => 'holdSteady',
                    ]);
            }));

        $this->assertSame($history, $history->add($action));
    }

    public function testAll()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(Adapter::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        usleep(100);
        $now2 = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                (new Map('string', File::class))
                    ->put(
                        md5('foo'),
                        new File\File(
                            md5('foo'),
                            new StringStream(json_encode([
                                'time' => $now,
                                'strategy' => 'holdSteady',
                            ]))
                        )
                    )
                    ->put(
                        md5('bar'),
                        new File\File(
                            md5('bar'),
                            new StringStream(json_encode([
                                'time' => $now2,
                                'strategy' => 'increase',
                            ]))
                        )
                    )
        );

        $actions = $history->all();

        $this->assertInstanceOf(StreamInterface::class, $actions);
        $this->assertSame(Action::class, (string) $actions->type());
        $this->assertCount(2, $actions);
        $action = $actions->current();
        $this->assertInstanceOf(Action::class, $action);
        $now = $clock->at($now);
        $this->assertTrue($action->time()->equals($now));
        $this->assertSame(Strategy::holdSteady(), $action->strategy());
        $actions->next();
        $action = $actions->current();
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
        sleep(1);
        $mark = $clock->now();
        sleep(1);
        $now2 = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                (new Map('string', File::class))
                    ->put(
                        md5('foo'),
                        new File\File(
                            md5('foo'),
                            new StringStream(json_encode([
                                'time' => $now,
                                'strategy' => 'increase',
                            ]))
                        )
                    )
                    ->put(
                        md5('bar'),
                        new File\File(
                            md5('bar'),
                            new StringStream(json_encode([
                                'time' => $now2,
                                'strategy' => 'decrease',
                            ]))
                        )
                    )
        );
        $filesystem
            ->expects($this->once())
            ->method('remove')
            ->with(md5('foo'));

        $this->assertSame($history, $history->keepUp($mark));
    }
}
