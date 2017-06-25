<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor\Cpu,
    Sensor,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface
};
use Innmind\Server\Status\{
    Server,
    Server\Cpu as ServerCpu,
    Server\Cpu\Percentage
};
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number,
    Algebra\Integer
};
use PHPUnit\Framework\TestCase;

class CpuTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Sensor::class,
            new Cpu(
                $this->createMock(TimeContinuumInterface::class),
                $this->createMock(Server::class),
                new Weight(new Number(0.5)),
                new Polynom
            )
        );
    }

    public function testInvokation()
    {
        $sensor = new Cpu(
            $clock = $this->createMock(TimeContinuumInterface::class),
            $server = $this->createMock(Server::class),
            $weight = new Weight(new Number(0.5)),
            (new Polynom)->withDegree(
                new Integer(1),
                new Number(0.5)
            )
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $server
            ->expects($this->once())
            ->method('cpu')
            ->willReturn(new ServerCpu(
                new Percentage(42),
                new Percentage(10),
                new Percentage(48)
            ));

        $measure = $sensor();

        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertSame($now, $measure->time());
        $this->assertSame(0.26, $measure->value()->value());
        $this->assertSame($weight, $measure->weight());
    }

    public function testLimitUpperBound()
    {
        $sensor = new Cpu(
            $clock = $this->createMock(TimeContinuumInterface::class),
            $server = $this->createMock(Server::class),
            $weight = new Weight(new Number(0.5)),
            (new Polynom(new Integer(2)))->withDegree(
                new Integer(1),
                new Integer(0)
            )
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $server
            ->expects($this->once())
            ->method('cpu')
            ->willReturn(new ServerCpu(
                new Percentage(42),
                new Percentage(10),
                new Percentage(48)
            ));

        $measure = $sensor();

        $this->assertSame(1, $measure->value()->value());
    }

    public function testLimitLowerBound()
    {
        $sensor = new Cpu(
            $clock = $this->createMock(TimeContinuumInterface::class),
            $server = $this->createMock(Server::class),
            $weight = new Weight(new Number(0.5)),
            (new Polynom(new Integer(-2)))->withDegree(
                new Integer(1),
                new Integer(0)
            )
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $server
            ->expects($this->once())
            ->method('cpu')
            ->willReturn(new ServerCpu(
                new Percentage(42),
                new Percentage(10),
                new Percentage(48)
            ));

        $measure = $sensor();

        $this->assertSame(0, $measure->value()->value());
    }
}
