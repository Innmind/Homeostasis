<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\{
    Actuator\Delegate,
    Actuator,
    State
};
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Actuator::class,
            new Delegate
        );
    }

    public function testDramaticDecrease()
    {
        $delegate = new Delegate(
            $mock1 = $this->createMock(Actuator::class),
            $mock2 = $this->createMock(Actuator::class)
        );
        $stream = Sequence::of(State::class);
        $mock1
            ->expects($this->once())
            ->method('dramaticDecrease')
            ->with($stream);
        $mock2
            ->expects($this->once())
            ->method('dramaticDecrease')
            ->with($stream);

        $this->assertNull($delegate->dramaticDecrease($stream));
    }

    public function testDecrease()
    {
        $delegate = new Delegate(
            $mock1 = $this->createMock(Actuator::class),
            $mock2 = $this->createMock(Actuator::class)
        );
        $stream = Sequence::of(State::class);
        $mock1
            ->expects($this->once())
            ->method('decrease')
            ->with($stream);
        $mock2
            ->expects($this->once())
            ->method('decrease')
            ->with($stream);

        $this->assertNull($delegate->decrease($stream));
    }

    public function testHoldSteady()
    {
        $delegate = new Delegate(
            $mock1 = $this->createMock(Actuator::class),
            $mock2 = $this->createMock(Actuator::class)
        );
        $stream = Sequence::of(State::class);
        $mock1
            ->expects($this->once())
            ->method('holdSteady')
            ->with($stream);
        $mock2
            ->expects($this->once())
            ->method('holdSteady')
            ->with($stream);

        $this->assertNull($delegate->holdSteady($stream));
    }

    public function testIncrease()
    {
        $delegate = new Delegate(
            $mock1 = $this->createMock(Actuator::class),
            $mock2 = $this->createMock(Actuator::class)
        );
        $stream = Sequence::of(State::class);
        $mock1
            ->expects($this->once())
            ->method('increase')
            ->with($stream);
        $mock2
            ->expects($this->once())
            ->method('increase')
            ->with($stream);

        $this->assertNull($delegate->increase($stream));
    }

    public function testDramaticIncrease()
    {
        $delegate = new Delegate(
            $mock1 = $this->createMock(Actuator::class),
            $mock2 = $this->createMock(Actuator::class)
        );
        $stream = Sequence::of(State::class);
        $mock1
            ->expects($this->once())
            ->method('dramaticIncrease')
            ->with($stream);
        $mock2
            ->expects($this->once())
            ->method('dramaticIncrease')
            ->with($stream);

        $this->assertNull($delegate->dramaticIncrease($stream));
    }
}
