<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\StateRepository;

use Innmind\Homeostasis\{
    StateRepository\Filesystem,
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
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    TimeContinuum\Earth
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    Map,
    SetInterface
};
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StateRepository::class,
            new Filesystem(
                $this->createMock(AdapterInterface::class),
                $this->createMock(TimeContinuumInterface::class)
            )
        );
    }

    public function testAdd()
    {
        $repository = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            $clock = new Earth
        );
        $now = $clock->now();
        $state = new State(
            new PointInTime($now),
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
                        'identity' => $now,
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

        $this->assertSame($repository, $repository->add($state));
    }

    public function testGet()
    {
        $repository = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('get')
            ->with(md5('foo'))
            ->willReturn(new File(
                md5('foo'),
                new StringStream(json_encode([
                    'identity' => $now,
                    'time' => $now,
                    'measures' => [
                        'cpu' => [
                            'time' => $now,
                            'value' => 0.5,
                            'weight' => 1,
                        ],
                    ],
                ]))
            ));
        $identity = $this->createMock(Identity::class);
        $identity
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');

        $state = $repository->get($identity);

        $this->assertInstanceOf(State::class, $state);
        $this->assertInstanceOf(PointInTime::class, $state->identity());
        $this->assertSame($now, (string) $state->identity());
        $now = $clock->at($now);
        $this->assertTrue($state->time()->equals($now));
        $measure = $state->factor('cpu');
        $this->assertTrue($measure->time()->equals($now));
        $this->assertSame(0.5, $measure->value()->value());
        $this->assertSame(1, $measure->weight()->value()->value());
    }

    public function testRemove()
    {
        $repository = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            new Earth
        );
        $identity = $this->createMock(Identity::class);
        $identity
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $filesystem
            ->expects($this->once())
            ->method('remove')
            ->with(md5('foo'));

        $this->assertSame($repository, $repository->remove($identity));
    }

    public function testAll()
    {
        $repository = new Filesystem(
            $filesystem = $this->createMock(AdapterInterface::class),
            $clock = new Earth
        );
        $now = $clock->now()->format(new ISO8601WithMilliseconds);
        $filesystem
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                (new Map('string', FileInterface::class))->put(
                    md5('foo'),
                    new File(
                        md5('foo'),
                        new StringStream(json_encode([
                            'identity' => $now,
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
        );

        $states = $repository->all();

        $this->assertInstanceOf(SetInterface::class, $states);
        $this->assertSame(State::class, (string) $states->type());
        $state = $states->current();
        $this->assertInstanceOf(State::class, $state);
        $this->assertInstanceOf(PointInTime::class, $state->identity());
        $this->assertSame($now, (string) $state->identity());
        $now = $clock->at($now);
        $this->assertTrue($state->time()->equals($now));
        $measure = $state->factor('cpu');
        $this->assertTrue($measure->time()->equals($now));
        $this->assertSame(0.5, $measure->value()->value());
        $this->assertSame(1, $measure->weight()->value()->value());
    }
}
