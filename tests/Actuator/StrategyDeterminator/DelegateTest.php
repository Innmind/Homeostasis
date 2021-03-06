<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\Delegate,
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Exception\StrategyNotDeterminable,
    Exception\RuntimeException,
    Exception\Exception,
};
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StrategyDeterminator::class,
            new Delegate
        );
    }

    public function testThrowByDefault()
    {
        $this->expectException(StrategyNotDeterminable::class);

        (new Delegate)(Sequence::of(State::class));
    }

    public function testInvokation()
    {
        $delegate = new Delegate(
            $first = $this->createMock(StrategyDeterminator::class),
            $second = $this->createMock(StrategyDeterminator::class),
            $third = $this->createMock(StrategyDeterminator::class)
        );
        $set = Sequence::of(State::class);
        $first
            ->expects($this->once())
            ->method('__invoke')
            ->with($set)
            ->will(
                $this->throwException(new StrategyNotDeterminable)
            );
        $second
            ->expects($this->once())
            ->method('__invoke')
            ->with($set)
            ->willReturn(Strategy::increase());
        $third
            ->expects($this->never())
            ->method('__invoke');

        $strategy = $delegate($set);

        $this->assertSame(Strategy::increase(), $strategy);
    }

    public function testNotAllExceptionsAreCaught()
    {
        $delegate = new Delegate(
            $determinator = $this->createMock(StrategyDeterminator::class)
        );
        $determinator
            ->expects($this->once())
            ->method('__invoke')
            ->will(
                $this->throwException(
                    new RuntimeException
                )
            );

        $this->expectException(Exception::class);

        $delegate(Sequence::of(State::class));
    }
}
