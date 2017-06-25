<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Factor;

use Innmind\Homeostasis\{
    Factor\Cpu,
    Factor,
    Sensor,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Server\Status\Server;
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Number\Number
};
use PHPUnit\Framework\TestCase;

class CpuTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Factor::class,
            $factor = new Cpu(
                $this->createMock(TimeContinuumInterface::class),
                $this->createMock(Server::class),
                new Weight(new Number(0.5)),
                new Polynom
            )
        );
        $this->assertSame('cpu', $factor->name());
        $this->assertInstanceOf(Sensor::class, $factor->sensor());
        $this->assertSame($factor->sensor(), $factor->sensor());
    }
}
