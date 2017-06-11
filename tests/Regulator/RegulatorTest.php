<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator\Regulator,
    Regulator as RegulatorInterface,
    Factor,
    StateRepository,
    Actuator,
    Actuator\StrategyDeterminator,
    Sensor,
    Sensor\Measure,
    Sensor\Measure\Weight,
    State,
    Strategy
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface
};
use Innmind\Math\Algebra\Number\Number;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RegulatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            RegulatorInterface::class,
            new Regulator(
                new Set(Factor::class),
                $this->createMock(StateRepository::class),
                $this->createMock(TimeContinuumInterface::class),
                $this->createMock(StrategyDeterminator::class),
                $this->createMock(Actuator::class)
            )
        );
    }

    public function testInvokation()
    {
        $regulate = new Regulator(
            (new Set(Factor::class))
                ->add($factor = $this->createMock(Factor::class)),
            $repository = $this->createMock(StateRepository::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
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
                    $this->createMock(PointInTimeInterface::class),
                    new Number(0.5),
                    new Weight(new Number(1))
                )
            );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $repository
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(function(State $state) use (&$built, $now, $measure): bool {
                $built = $state;

                return $state->time() === $now &&
                    $state->factor('cpu') === $measure;
            }))
            ->will($this->returnSelf());
        $repository
            ->expects($this->once())
            ->method('all')
            ->will($this->returnCallback(function() use (&$built) {
                return (new Set(State::class))->add($built);
            }));
        $determinator
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function(Set $set) use (&$built) {
                return $set->size() === 1 &&
                    $set->current() === $built;
            }))
            ->willReturn(Strategy::increase());
        $actuator
            ->expects($this->once())
            ->method('increase')
            ->with($this->callback(function(Set $set) use (&$built) {
                return $set->size() === 1 &&
                    $set->current() === $built;
            }));

        $this->assertSame(Strategy::increase(), $regulate());
    }
}
