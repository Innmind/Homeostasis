<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\Delegate,
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Exception\StrategyNotDeterminable,
    Exception\RuntimeException
};
use Innmind\Immutable\Stream;
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

    /**
     * @expectedException Innmind\Homeostasis\Exception\StrategyNotDeterminable
     */
    public function testThrowByDefault()
    {
        (new Delegate)(new Stream(State::class));
    }

    public function testInvokation()
    {
        $delegate = new Delegate(
            $first = $this->createMock(StrategyDeterminator::class),
            $second = $this->createMock(StrategyDeterminator::class),
            $third = $this->createMock(StrategyDeterminator::class)
        );
        $set = new Stream(State::class);
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

    /**
     * @expectedException Innmind\Homeostasis\Exception\Exception
     */
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

        $delegate(new Stream(State::class));
    }
}
