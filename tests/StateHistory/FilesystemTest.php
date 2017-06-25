<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\StateHistory;

use Innmind\Homeostasis\{
    StateHistory\Filesystem,
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
            StateHistory::class,
            new Filesystem(
                $this->createMock(AdapterInterface::class),
                $this->createMock(TimeContinuumInterface::class)
            )
        );
    }

    public function testAdd()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            $clock = new Earth
        );
        $now = $clock->now();
        $state = new State(
            $now,
            (new Map('string', Measure::class))->put(
                'cpu',
                new Measure(
                    $now,
                    new Number(0.5),
                    new Weight(new Number(1))
                )
            )
        );
        $now = $now->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(function(File $file) use ($now): bool {
                return (string) $file->name() === md5($now) &&
                    (string) $file->content() === json_encode([
                        'time' => $now,
                        'measures' => [
                            'cpu' => [
                                'time' => $now,
                                'value' => 0.5,
                                'weight' => 1.0,
                            ],
                        ],
                    ]);
            }));

        $this->assertSame($history, $history->add($state));
    }

    public function testAll()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        usleep(100);
        $now2 = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                (new Map('string', FileInterface::class))
                    ->put(
                        md5('foo'),
                        new File(
                            md5('foo'),
                            new StringStream(json_encode([
                                'time' => $now,
                                'measures' => [
                                    'cpu' => [
                                        'time' => $now,
                                        'value' => 0.5,
                                        'weight' => 1,
                                    ],
                                ],
                            ]))
                        )
                    )
                    ->put(
                        md5('bar'),
                        new File(
                            md5('bar'),
                            new StringStream(json_encode([
                                'time' => $now2,
                                'measures' => [
                                    'cpu' => [
                                        'time' => $now2,
                                        'value' => 0.4,
                                        'weight' => 1,
                                    ],
                                ],
                            ]))
                        )
                    )
        );

        $states = $history->all();

        $this->assertInstanceOf(StreamInterface::class, $states);
        $this->assertSame(State::class, (string) $states->type());
        $this->assertCount(2, $states);
        $state = $states->current();
        $this->assertInstanceOf(State::class, $state);
        $now = $clock->at($now);
        $this->assertTrue($state->time()->equals($now));
        $measure = $state->factor('cpu');
        $this->assertTrue($measure->time()->equals($now));
        $this->assertSame(0.5, $measure->value()->value());
        $this->assertSame(1, $measure->weight()->value()->value());
        $states->next();
        $state = $states->current();
        $this->assertInstanceOf(State::class, $state);
        $now2 = $clock->at($now2);
        $this->assertTrue($state->time()->equals($now2));
        $measure = $state->factor('cpu');
        $this->assertTrue($measure->time()->equals($now2));
        $this->assertSame(0.4, $measure->value()->value());
        $this->assertSame(1, $measure->weight()->value()->value());
    }

    public function testKeepUp()
    {
        $history = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
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
                (new Map('string', FileInterface::class))
                    ->put(
                        md5('foo'),
                        new File(
                            md5('foo'),
                            new StringStream(json_encode([
                                'time' => $now,
                                'measures' => [
                                    'cpu' => [
                                        'time' => $now,
                                        'value' => 0.5,
                                        'weight' => 1,
                                    ],
                                ],
                            ]))
                        )
                    )
                    ->put(
                        md5('bar'),
                        new File(
                            md5('bar'),
                            new StringStream(json_encode([
                                'time' => $now2,
                                'measures' => [
                                    'cpu' => [
                                        'time' => $now2,
                                        'value' => 0.4,
                                        'weight' => 1,
                                    ],
                                ],
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
