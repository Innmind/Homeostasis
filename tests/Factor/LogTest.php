<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Factor;

use Innmind\Homeostasis\{
    Factor\Log,
    Factor,
    Sensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\Clock;
use Innmind\LogReader\Reader;
use Innmind\Filesystem\Adapter;
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number
};
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Factor::class,
            $factor = new Log(
                $this->createMock(Clock::class),
                $this->createMock(Reader::class),
                $this->createMock(Adapter::class),
                new Weight(new Number(0.5)),
                new Polynom,
                static function() {},
                'foo'
            )
        );
        $this->assertSame('log_foo', $factor->name());
        $this->assertInstanceOf(Sensor::class, $factor->sensor());
        $this->assertSame($factor->sensor(), $factor->sensor());
    }
}
