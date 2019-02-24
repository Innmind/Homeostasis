<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    Exception\SumOfWeightsMustBeOne,
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

    public function testThrowWhenInvalidMeasuresKeys()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, Innmind\Homeostasis\Sensor\Measure>');

        new State(
            $this->createMock(PointInTimeInterface::class),
            new Map('int', Measure::class)
        );
    }

    public function testThrowWhenInvalidMeasuresValues()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, Innmind\Homeostasis\Sensor\Measure>');

        new State(
            $this->createMock(PointInTimeInterface::class),
            new Map('string', 'int')
        );
    }

    public function testThrowWhenSumOfWeightsIsDifferentFromOne()
    {
        $this->expectException(SumOfWeightsMustBeOne::class);

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
