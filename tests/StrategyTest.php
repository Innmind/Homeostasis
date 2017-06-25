<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\Strategy;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    public function testDramaticDecrease()
    {
        $strategy = Strategy::dramaticDecrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('dramaticDecrease', (string) $strategy);
        $this->assertSame($strategy, Strategy::dramaticDecrease());
    }

    public function testDecrease()
    {
        $strategy = Strategy::decrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('decrease', (string) $strategy);
        $this->assertSame($strategy, Strategy::decrease());
    }

    public function testHoldSteady()
    {
        $strategy = Strategy::holdSteady();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('holdSteady', (string) $strategy);
        $this->assertSame($strategy, Strategy::holdSteady());
    }

    public function testIncrease()
    {
        $strategy = Strategy::increase();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('increase', (string) $strategy);
        $this->assertSame($strategy, Strategy::increase());
    }

    public function testDramaticIncrease()
    {
        $strategy = Strategy::dramaticIncrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('dramaticIncrease', (string) $strategy);
        $this->assertSame($strategy, Strategy::dramaticIncrease());
    }
}
