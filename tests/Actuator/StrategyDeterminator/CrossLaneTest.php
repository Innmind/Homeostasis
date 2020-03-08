<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\CrossLane,
    Actuator\StrategyDeterminator,
    Math\Dataset\Augment,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    Strategy
};
use Innmind\Math\Algebra\{
    Integer,
    Number\Number
};
use Innmind\TimeContinuum\{
    Earth\Clock as Earth,
    PointInTime,
};
use Innmind\Immutable\{
    Map,
    Sequence,
};
use PHPUnit\Framework\TestCase;

class CrossLaneTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StrategyDeterminator::class,
            new CrossLane(new Augment(new Integer(1)))
        );
    }

    public function testDramaticDecrease()
    {
        $determinate = new CrossLane(new Augment(new Integer(1)));
        $clock = new Earth;

        $strategy = $determinate(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.1),
                                new Weight(new Number(1))
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
                                new Number(0.9),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );

        $this->assertSame(Strategy::dramaticDecrease(), $strategy);
    }

    public function testDramaticIncrease()
    {
        $determinate = new CrossLane(new Augment(new Integer(1)));
        $clock = new Earth;

        $strategy = $determinate(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.9),
                                new Weight(new Number(1))
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
                                new Number(0.1),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );

        $this->assertSame(Strategy::dramaticIncrease(), $strategy);
    }

    public function testDecrease()
    {
        $determinate = new CrossLane(new Augment(new Integer(1)));
        $clock = new Earth;

        $strategy = $determinate(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.4),
                                new Weight(new Number(1))
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
                                new Number(0.6),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );

        $this->assertSame(Strategy::decrease(), $strategy);
    }

    public function testIncrease()
    {
        $determinate = new CrossLane(new Augment(new Integer(1)));
        $clock = new Earth;

        $strategy = $determinate(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.6),
                                new Weight(new Number(1))
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
                                new Number(0.4),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );

        $this->assertSame(Strategy::increase(), $strategy);
    }
}
