<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\HoldSteadyOnError,
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Exception\RuntimeException
};
use Innmind\Immutable\Stream;
use PHPUnit\Framework\TestCase;

class HoldSteadyOnErrorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StrategyDeterminator::class,
            new HoldSteadyOnError(
                $this->createMock(StrategyDeterminator::class)
            )
        );
    }

    public function testInvokation()
    {
        $determine = new HoldSteadyOnError(
            $first = $this->createMock(StrategyDeterminator::class)
        );
        $set = new Stream(State::class);
        $first
            ->expects($this->once())
            ->method('__invoke')
            ->with($set)
            ->willReturn(Strategy::increase());

        $strategy = $determine($set);

        $this->assertSame(Strategy::increase(), $strategy);
    }

    public function testHoldSteadyOnError()
    {
        $determine = new HoldSteadyOnError(
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

        $strategy = $determine(new Stream(State::class));

        $this->assertSame(Strategy::holdSteady(), $strategy);
    }
}
