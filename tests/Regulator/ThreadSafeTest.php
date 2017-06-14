<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator\ThreadSafe,
    Regulator,
    Strategy
};
use Symfony\Component\Filesystem\LockHandler;
use PHPUnit\Framework\TestCase;

class ThreadSafeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Regulator::class,
            new ThreadSafe(
                $this->createMock(Regulator::class)
            )
        );
    }

    public function testInvokation()
    {
        $regulate = new ThreadSafe(
            $inner = $this->createMock(Regulator::class)
        );
        $inner
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Strategy::increase());

        $strategy = $regulate();

        $this->assertSame(Strategy::increase(), $strategy);

        $handler = new LockHandler('homeostasis');

        $this->assertTrue($handler->lock());
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\HomeostasisAlreadyInProcess
     */
    public function testThrowWhenHomeostasisAlreadyInProcess()
    {
        $handler = new LockHandler('homeostasis');
        $handler->lock();

        $regulate = new ThreadSafe(
            $inner = $this->createMock(Regulator::class)
        );
        $inner
            ->expects($this->never())
            ->method('__invoke');

        $regulate();
    }
}
