<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator\ThreadSafe,
    Regulator,
    Strategy
};
use Symfony\Component\Lock\{
    Factory,
    Store\FlockStore
};
use PHPUnit\Framework\TestCase;

class ThreadSafeTest extends TestCase
{
    private $lock;

    public function setUp()
    {
        $this->lock = new Factory(new FlockStore);
    }

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

        $lock = $this->lock->createLock('homeostasis');

        $this->assertTrue($lock->acquire());
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\HomeostasisAlreadyInProcess
     */
    public function testThrowWhenHomeostasisAlreadyInProcess()
    {
        $lock = $this->lock->createLock('homeostasis');
        $lock->acquire();

        $regulate = new ThreadSafe(
            $inner = $this->createMock(Regulator::class)
        );
        $inner
            ->expects($this->never())
            ->method('__invoke');

        $regulate();
    }
}
