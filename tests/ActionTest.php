<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Action,
    Strategy
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Math\Algebra\Integer;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    public function testInterface()
    {
        $action = new Action(
            $time = $this->createMock(PointInTime::class),
            Strategy::holdSteady()
        );

        $this->assertSame($time, $action->time());
        $this->assertSame(Strategy::holdSteady(), $action->strategy());
    }

    /**
     * @dataProvider cases
     */
    public function testVariation($current, $previous, $expected)
    {
        $variation = (new Action(
            $this->createMock(PointInTime::class),
            Strategy::{$current}()
        ))->variation(
            new Action(
                $this->createMock(PointInTime::class),
                Strategy::{$previous}()
            )
        );

        $this->assertInstanceOf(Integer::class, $variation);
        $this->assertSame($expected, $variation->value());
    }

    public function cases(): array
    {
        return [
            ['dramaticDecrease', 'dramaticDecrease', 0],
            ['dramaticDecrease', 'decrease', 1],
            ['dramaticDecrease', 'holdSteady', 1],
            ['dramaticDecrease', 'increase', 1],
            ['dramaticDecrease', 'dramaticIncrease', 1],
            ['decrease', 'dramaticDecrease', -1],
            ['decrease', 'decrease', 0],
            ['decrease', 'holdSteady', 0],
            ['decrease', 'increase', 0],
            ['decrease', 'dramaticIncrease', 1],
            ['holdSteady', 'dramaticDecrease', -1],
            ['holdSteady', 'decrease', 0],
            ['holdSteady', 'holdSteady', 0],
            ['holdSteady', 'increase', 0],
            ['holdSteady', 'dramaticIncrease', 1],
            ['increase', 'dramaticDecrease', -1],
            ['increase', 'decrease', 0],
            ['increase', 'holdSteady', 0],
            ['increase', 'increase', 0],
            ['increase', 'dramaticIncrease', 1],
            ['dramaticIncrease', 'dramaticDecrease', -1],
            ['dramaticIncrease', 'decrease', -1],
            ['dramaticIncrease', 'holdSteady', -1],
            ['dramaticIncrease', 'increase', -1],
            ['dramaticIncrease', 'dramaticIncrease', 0],
        ];
    }
}
