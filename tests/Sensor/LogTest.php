<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor\Log,
    Sensor,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\LogReader\{
    Reader,
    Log as LogLine,
    Reader\Synchronous,
    Reader\LineParser\Monolog,
};
use Innmind\Filesystem\{
    Adapter,
    Adapter\Filesystem,
    Adapter\InMemory
};
use Innmind\Url\Path;
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number,
    Algebra\Integer
};
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Sensor::class,
            new Log(
                $this->createMock(Clock::class),
                $this->createMock(Reader::class),
                $this->createMock(Adapter::class),
                new Weight(new Number(0.5)),
                new Polynom,
                static function() {}
            )
        );
    }

    public function testInvokation()
    {
        $sensor = new Log(
            $clock = $this->createMock(Clock::class),
            new Synchronous(new Monolog($clock)),
            new Filesystem(Path::of('fixtures/logs/')),
            $weight = new Weight(new Number(0.5)),
            (new Polynom)->withDegree(
                new Integer(1),
                new Number(0.5)
            ),
            static function(LogLine $log): bool {
                return $log->attributes()->contains('level') &&
                    \in_array($log->attributes()->get('level')->value(), ['emergency', 'critical'], true);
            }
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));

        $measure = $sensor();

        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertSame($now, $measure->time());
        $this->assertSame(0.125, $measure->value()->value());
        $this->assertSame($weight, $measure->weight());
    }

    public function testInvokationWhenNoLog()
    {
        $sensor = new Log(
            $clock = $this->createMock(Clock::class),
            new Synchronous(new Monolog($clock)),
            new InMemory,
            $weight = new Weight(new Number(0.5)),
            (new Polynom)->withDegree(
                new Integer(1),
                new Number(0.5)
            ),
            static function(LogLine $log): bool {
                return $log->attributes()->contains('level') &&
                    \in_array($log->attributes()->get('level')->value(), ['emergency', 'critical'], true);
            }
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));

        $measure = $sensor();

        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertSame($now, $measure->time());
        $this->assertSame(0.0, $measure->value()->value());
        $this->assertSame($weight, $measure->weight());
    }

    public function testLimitUpperBound()
    {
        $sensor = new Log(
            $clock = $this->createMock(Clock::class),
            new Synchronous(new Monolog($clock)),
            new Filesystem(Path::of('fixtures/logs/')),
            $weight = new Weight(new Number(0.5)),
            (new Polynom(new Integer(2)))->withDegree(
                new Integer(1),
                new Number(0)
            ),
            static function(LogLine $log): bool {
                return $log->attributes()->contains('level') &&
                    \in_array($log->attributes()->get('level')->value(), ['emergency', 'critical'], true);
            }
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));

        $measure = $sensor();

        $this->assertSame(1, $measure->value()->value());
    }

    public function testLimitLowerBound()
    {
        $sensor = new Log(
            $clock = $this->createMock(Clock::class),
            new Synchronous(new Monolog($clock)),
            new Filesystem(Path::of('fixtures/logs/')),
            $weight = new Weight(new Number(0.5)),
            (new Polynom(new Integer(-2)))->withDegree(
                new Integer(1),
                new Number(0)
            ),
            static function(LogLine $log): bool {
                return $log->attributes()->contains('level') &&
                    \in_array($log->attributes()->get('level')->value(), ['emergency', 'critical'], true);
            }
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));

        $measure = $sensor();

        $this->assertSame(0, $measure->value()->value());
    }
}
