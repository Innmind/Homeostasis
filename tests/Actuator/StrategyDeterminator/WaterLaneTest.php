<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\WaterLane,
    Actuator\StrategyDeterminator,
    Strategy,
    Math\Dataset\Augment,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    Exception\StrategyNotDeterminable,
};
use Innmind\Math\{
    Algebra\Number\Number,
    Algebra\Integer,
    DefinitionSet\Range
};
use Innmind\TimeContinuum\{
    Earth\Clock as Earth,
    PointInTime
};
use Innmind\Immutable\{
    Sequence,
    Map
};
use PHPUnit\Framework\TestCase;

class WaterLaneTest extends TestCase
{
    private $lane;

    public function setUp(): void
    {
        $this->lane = new WaterLane(
            Range::inclusive(new Number(0.4), new Number(0.6)),
            new Augment(new Integer(1)),
            Strategy::increase(),
            Strategy::decrease()
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(StrategyDeterminator::class, $this->lane);
    }

    public function testDecrease()
    {
        $clock = new Earth;
        $strategy = ($this->lane)(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.45),
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
                                new Number(0.47),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.400+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.5),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.500+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.52),
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
        $clock = new Earth;
        $strategy = ($this->lane)(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.52),
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
                                new Number(0.5),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.400+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.48),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.500+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.47),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );

        $this->assertSame(Strategy::increase(), $strategy);
    }

    public function testThrowWhenNotInLane()
    {
        $clock = new Earth;

        $this->expectException(StrategyNotDeterminable::class);

        ($this->lane)(
            Sequence::of(
                State::class,
                new State(
                    $clock->at('2017-01-01T00:00:00.200+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.15),
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
                                new Number(0.2),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.400+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.25),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.500+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.3),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );
    }

    public function testThrowWhenCrossInLane()
    {
        $clock = new Earth;

        $this->expectException(StrategyNotDeterminable::class);

        ($this->lane)(
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
                                new Number(0.45),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.400+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.5),
                                new Weight(new Number(1))
                            )
                        )
                ),
                new State(
                    $clock->at('2017-01-01T00:00:00.500+0000'),
                    Map::of('string', Measure::class)
                        (
                            'cpu',
                            new Measure(
                                $this->createMock(PointInTime::class),
                                new Number(0.55),
                                new Weight(new Number(1))
                            )
                        )
                ),
            ),
        );
    }
}
