<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator\ModulateStateHistory,
    Regulator,
    ActionHistory,
    StateHistory,
    Strategy,
    Action
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
    ElapsedPeriod,
    Period\Earth\Millisecond
};
use Innmind\Immutable\Stream;
use PHPUnit\Framework\TestCase;

class ModulateStateHistoryTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Regulator::class,
            new ModulateStateHistory(
                $this->createMock(Regulator::class),
                $this->createMock(ActionHistory::class),
                $this->createMock(StateHistory::class),
                $this->createMock(TimeContinuumInterface::class),
                new ElapsedPeriod(0),
                new ElapsedPeriod(0)
            )
        );
    }

    public function testShortenStateHistoryWhenGloballyStable()
    {
        $regulate = new ModulateStateHistory(
            $inner = $this->createMock(Regulator::class),
            $actions = $this->createMock(ActionHistory::class),
            $states = $this->createMock(StateHistory::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(200),
            new ElapsedPeriod(20)
        );
        $clock
            ->expects($this->exactly(3))
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $inner
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Strategy::decrease());
        $actions
            ->expects($this->at(0))
            ->method('add')
            ->with($this->callback(static function(Action $action) use ($now): bool {
                return $action->time() === $now &&
                    $action->strategy() === Strategy::decrease();
            }));
        $now
            ->expects($this->at(0))
            ->method('goBack')
            ->with($this->callback(static function(Millisecond $interval): bool {
                return $interval->milliseconds() === 200;
            }))
            ->willReturn($max = $this->createMock(PointInTimeInterface::class));
        $actions
            ->expects($this->at(1))
            ->method('keepUp')
            ->with($max);
        $states
            ->expects($this->at(0))
            ->method('keepUp')
            ->with($max);
        $actions
            ->expects($this->at(2))
            ->method('all')
            ->willReturn(
                (new Stream(Action::class))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::increase()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::increase()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::increase()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::increase()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::dramaticIncrease()
                    ))
            );
        $now
            ->expects($this->at(1))
            ->method('goBack')
            ->with($this->callback(static function(Millisecond $interval): bool {
                return $interval->milliseconds() === 20;
            }))
            ->willReturn($min = $this->createMock(PointInTimeInterface::class));
        $actions
            ->expects($this->at(3))
            ->method('keepUp')
            ->with($max);
        $actions
            ->expects($this->exactly(2))
            ->method('keepUp');
        $states
            ->expects($this->at(1))
            ->method('keepUp')
            ->with($max);
        $states
            ->expects($this->exactly(2))
            ->method('keepUp');

        $this->assertSame(Strategy::decrease(), $regulate());
    }

    public function testOnlyKeepMaxHistoryAllowedWhenEratic()
    {
        $regulate = new ModulateStateHistory(
            $inner = $this->createMock(Regulator::class),
            $actions = $this->createMock(ActionHistory::class),
            $states = $this->createMock(StateHistory::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(200),
            new ElapsedPeriod(20)
        );
        $clock
            ->expects($this->exactly(2))
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $inner
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Strategy::decrease());
        $actions
            ->expects($this->at(0))
            ->method('add')
            ->with($this->callback(static function(Action $action) use ($now): bool {
                return $action->time() === $now &&
                    $action->strategy() === Strategy::decrease();
            }));
        $now
            ->expects($this->once())
            ->method('goBack')
            ->with($this->callback(static function(Millisecond $interval): bool {
                return $interval->milliseconds() === 200;
            }))
            ->willReturn($max = $this->createMock(PointInTimeInterface::class));
        $actions
            ->expects($this->at(1))
            ->method('keepUp')
            ->with($max);
        $actions
            ->expects($this->once())
            ->method('keepUp');
        $states
            ->expects($this->at(0))
            ->method('keepUp')
            ->with($max);
        $states
            ->expects($this->once())
            ->method('keepUp');
        $actions
            ->expects($this->at(2))
            ->method('all')
            ->willReturn(
                (new Stream(Action::class))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::dramaticDecrease()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::decrease()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::holdSteady()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::increase()
                    ))
                    ->add(new Action(
                        $this->createMock(PointInTimeInterface::class),
                        Strategy::dramaticDecrease()
                    ))
            );

        $this->assertSame(Strategy::decrease(), $regulate());
    }
}
