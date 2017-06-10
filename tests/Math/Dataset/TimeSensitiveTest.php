<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\{
    Math\Dataset\TimeSensitive,
    State,
    State\Identity,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\TimeContinuum\{
    TimeContinuum\Earth,
    PointInTimeInterface
};
use Innmind\Math\{
    Algebra\Number,
    Regression\Dataset
};
use Innmind\Immutable\{
    Set,
    Map
};
use PHPUnit\Framework\TestCase;

class TimeSensitiveTest extends TestCase
{
    public function testInvokation()
    {
        $clock = new Earth;
        $states = (new Set(State::class))
            ->add(
                new State(
                    $this->createMock(Identity::class),
                    $clock->at('2017-01-01T00:00:00.300+0000'),
                    (new Map('string', Measure::class))
                        ->put(
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTimeInterface::class),
                                new Number\Number(0.2),
                                new Weight(new Number\Number(1))
                            )
                        )
                )
            )
            ->add(
                new State(
                    $this->createMock(Identity::class),
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    (new Map('string', Measure::class))
                        ->put(
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTimeInterface::class),
                                new Number\Number(0.4),
                                new Weight(new Number\Number(1))
                            )
                        )
                )
            )
            ->add(
                new State(
                    $this->createMock(Identity::class),
                    $clock->at('2017-01-01T00:00:00.000+0000'),
                    (new Map('string', Measure::class))
                        ->put(
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTimeInterface::class),
                                new Number\Number(0.6),
                                new Weight(new Number\Number(1))
                            )
                        )
                )
            )
            ->add(
                new State(
                    $this->createMock(Identity::class),
                    $clock->at('2017-01-01T00:00:00.100+0000'),
                    (new Map('string', Measure::class))
                        ->put(
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTimeInterface::class),
                                new Number\Number(0.8),
                                new Weight(new Number\Number(1))
                            )
                        )
                )
            );

        $dataset = (new TimeSensitive)($states);

        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertSame(
            [
                [0, 0.6],
                [100, 0.8],
                [200, 0.4],
                [300, 0.2],
            ],
            $dataset->toArray()
        );
    }
}
