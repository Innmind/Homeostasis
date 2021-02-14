<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator\Regulator,
    Regulator as RegulatorInterface,
    Factor,
    StateHistory,
    Actuator,
    Actuator\StrategyDeterminator,
    Sensor,
    Sensor\Measure,
    Sensor\Measure\Weight,
    State,
    Strategy
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\{
    Set,
    Sequence,
};
use PHPUnit\Framework\TestCase;

class RegulatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            RegulatorInterface::class,
            new Regulator(
                Set::of(Factor::class),
                $this->createMock(StateHistory::class),
                $this->createMock(Clock::class),
                $this->createMock(StrategyDeterminator::class),
                $this->createMock(Actuator::class)
            )
        );
    }

    public function testInvokation()
    {
        $regulate = new Regulator(
            Set::of(Factor::class, $factor = $this->createMock(Factor::class)),
            $history = $this->createMock(StateHistory::class),
            $clock = $this->createMock(Clock::class),
            $determinator = $this->createMock(StrategyDeterminator::class),
            $actuator = $this->createMock(Actuator::class)
        );
        $built = null;
        $factor
            ->expects($this->once())
            ->method('name')
            ->willReturn('cpu');
        $factor
            ->expects($this->once())
            ->method('sensor')
            ->willReturn($sensor = $this->createMock(Sensor::class));
        $sensor
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                $measure = new Measure(
                    $this->createMock(PointInTime::class),
                    new Number(0.5),
                    new Weight(new Number(1))
                )
            );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));
        $history
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(static function(State $state) use (&$built, $now, $measure): bool {
                $built = $state;

                return $state->time() === $now &&
                    $state->factor('cpu') === $measure;
            }))
            ->will($this->returnSelf());
        $history
            ->expects($this->once())
            ->method('all')
            ->will($this->returnCallback(static function() use (&$built) {
                return Sequence::of(State::class, $built);
            }));
        $determinator
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function(Sequence $stream) use (&$built) {
                return $stream->size() === 1 &&
                    $stream->first() === $built;
            }))
            ->willReturn(Strategy::increase());
        $actuator
            ->expects($this->once())
            ->method('increase')
            ->with($this->callback(static function(Sequence $stream) use (&$built) {
                return $stream->size() === 1 &&
                    $stream->first() === $built;
            }));

        $this->assertSame(Strategy::increase(), $regulate());
    }
}
