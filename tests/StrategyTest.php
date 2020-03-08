<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\Strategy;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class StrategyTest extends TestCase
{
    use BlackBox;

    public function testDramaticDecrease()
    {
        $strategy = Strategy::dramaticDecrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('dramaticDecrease', $strategy->toString());
        $this->assertSame($strategy, Strategy::dramaticDecrease());
    }

    public function testDecrease()
    {
        $strategy = Strategy::decrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('decrease', $strategy->toString());
        $this->assertSame($strategy, Strategy::decrease());
    }

    public function testHoldSteady()
    {
        $strategy = Strategy::holdSteady();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('holdSteady', $strategy->toString());
        $this->assertSame($strategy, Strategy::holdSteady());
    }

    public function testIncrease()
    {
        $strategy = Strategy::increase();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('increase', $strategy->toString());
        $this->assertSame($strategy, Strategy::increase());
    }

    public function testDramaticIncrease()
    {
        $strategy = Strategy::dramaticIncrease();

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertSame('dramaticIncrease', $strategy->toString());
        $this->assertSame($strategy, Strategy::dramaticIncrease());
    }

    public function testOfMethodAlwaysReturnTheSameInstanceAsDedicatedMethods()
    {
        $this
            ->forAll(Set\Elements::of(
                'dramaticDecrease',
                'decrease',
                'holdSteady',
                'increase',
                'dramaticIncrease',
            ))
            ->then(function($strategy) {
                $this->assertSame(Strategy::$strategy(), Strategy::of($strategy));
            });
    }
}
