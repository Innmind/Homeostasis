<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    Exception\SumOfWeightsMustBeOne,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Math\Algebra\Number;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    public function testInterface()
    {
        $state = new State(
            $time = $this->createMock(PointInTime::class),
            $measures = Map::of('string', Measure::class)
                (
                    'cpu',
                    $cpu = new Measure(
                        $this->createMock(PointInTime::class),
                        new Number\Number(0.4),
                        new Weight(new Number\Number(0.7))
                    )
                )
                (
                    'error',
                    $error = new Measure(
                        $this->createMock(PointInTime::class),
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

    public function testThrowWhenInvalidMeasuresKeys()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, Innmind\Homeostasis\Sensor\Measure>');

        new State(
            $this->createMock(PointInTime::class),
            Map::of('int', Measure::class)
        );
    }

    public function testThrowWhenInvalidMeasuresValues()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, Innmind\Homeostasis\Sensor\Measure>');

        new State(
            $this->createMock(PointInTime::class),
            Map::of('string', 'int')
        );
    }

    public function testThrowWhenSumOfWeightsIsDifferentFromOne()
    {
        $this->expectException(SumOfWeightsMustBeOne::class);

        new State(
            $this->createMock(PointInTime::class),
            Map::of('string', Measure::class)
                (
                    'cpu',
                    new Measure(
                        $this->createMock(PointInTime::class),
                        new Number\Number(0.4),
                        new Weight(new Number\Number(0.7))
                    )
                )
        );
    }
}
