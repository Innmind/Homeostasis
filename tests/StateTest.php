<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    State,
    State\Identity,
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
            $identity = $this->createMock(Identity::class),
            (new Map('string', Measure::class))
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

        $this->assertSame($identity, $state->identity());
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
     * @expectedException Innmind\Homeostasis\Exception\InvalidMeasures
     */
    public function testThrowWhenInvalidMeasuresKeys()
    {
        new State(
            $this->createMock(Identity::class),
            new Map('int', Measure::class)
        );
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\InvalidMeasures
     */
    public function testThrowWhenInvalidMeasuresValues()
    {
        new State(
            $this->createMock(Identity::class),
            new Map('string', 'int')
        );
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\SumOfWeightsMustBeOne
     */
    public function testThrowWhenSumOfWeightsIsDifferentFromOne()
    {
        new State(
            $this->createMock(Identity::class),
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
