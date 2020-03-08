<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\{
    Math\Dataset\TimeSensitive,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\{
    Earth\Clock as Earth,
    PointInTime,
};
use Innmind\Math\{
    Algebra\Number,
    Regression\Dataset
};
use Innmind\Immutable\{
    Sequence,
    Map,
};
use PHPUnit\Framework\TestCase;

class TimeSensitiveTest extends TestCase
{
    public function testInvokation()
    {
        $clock = new Earth;
        $states = Sequence::of(
            State::class,
            new State(
                $clock->at('2017-01-01T00:00:00.000+0000'),
                Map::of('string', Measure::class)
                    (
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTime::class),
                            new Number\Number(0.6),
                            new Weight(new Number\Number(1))
                        )
                    )
            ),
            new State(
                $clock->at('2017-01-01T00:00:00.100+0000'),
                Map::of('string', Measure::class)
                    (
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTime::class),
                            new Number\Number(0.8),
                            new Weight(new Number\Number(1))
                        )
                    )
            ),
            new State(
                $clock->at('2017-01-01T00:00:00.200+0000'),
                Map::of('string', Measure::class)
                    (
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTime::class),
                            new Number\Number(0.4),
                            new Weight(new Number\Number(1))
                        )
                    )
            ),
            new State(
                $clock->at('2017-01-01T00:00:00.300+0000'),
                Map::of('string', Measure::class)
                    (
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTime::class),
                            new Number\Number(0.2),
                            new Weight(new Number\Number(1))
                        )
                    )
            ),
        );

        $dataset = (new TimeSensitive)($states);

        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertSame(
            [
                [0.0, 0.6],
                [1.0, 0.8],
                [2.0, 0.4],
                [3.0, 0.2],
            ],
            $dataset->toArray()
        );
    }
}
