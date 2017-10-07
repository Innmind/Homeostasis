<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    State,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Math\Algebra\Number;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    public function testInterface()
    {
        $state = new State(
            $time = $this->createMock(PointInTimeInterface::class),
            $measures = (new Map('string', Measure::class))
                ->put(
                    'cpu',
                    $cpu = new Measure(
                        $this->createMock(PointInTimeInterface::class),
                        new Number\Number(0.4),
                        new Weight(new Number\Number(0.7))
                    )
                )
                ->put(
                    'error',
                    $error = new Measure(
                        $this->createMock(PointInTimeInterface::class),
                        new Number\Number(0.9),
                        new Weight(new Number\Number(0.3))
                    )
                )
        );

        $this->assertSame($time, $state->time());
        $this->assertSame($measures, $state->measures());
        $this->assertSame($cpu, $state->factor('cpu'));
        $this->assertSame($error, $state->factor('error'));
        $this->assertInstanceOf(Number::class, $state->value());
        $this->assertSame(0.275, $state->value()->value());
        $this->assertSame(
            '((0.4 x 0.7) + (0.9 x 0.3)) ÷ 2',
            (string) $state
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 must be of type MapInterface<string, Innmind\Homeostasis\Sensor\Measure>
     */
    public function testThrowWhenInvalidMeasuresKeys()
    {
        new State(
            $this->createMock(PointInTimeInterface::class),
            new Map('int', Measure::class)
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 must be of type MapInterface<string, Innmind\Homeostasis\Sensor\Measure>
     */
    public function testThrowWhenInvalidMeasuresValues()
    {
        new State(
            $this->createMock(PointInTimeInterface::class),
            new Map('string', 'int')
        );
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\SumOfWeightsMustBeOne
     */
    public function testThrowWhenSumOfWeightsIsDifferentFromOne()
    {
        new State(
            $this->createMock(PointInTimeInterface::class),
            (new Map('string', Measure::class))
                ->put(
                    'cpu',
                    new Measure(
                        $this->createMock(PointInTimeInterface::class),
                        new Number\Number(0.4),
                        new Weight(new Number\Number(0.7))
                    )
                )
        );
    }
}
