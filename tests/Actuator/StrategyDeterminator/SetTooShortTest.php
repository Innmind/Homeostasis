<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\SetTooShort,
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Sensor\Measure,
    Sensor\Measure\Weight,
    Exception\StrategyNotDeterminable,
};
use Innmind\Math\{
    DefinitionSet\Set as DefinitionSet,
    DefinitionSet\Range,
    Algebra\Number\Number
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Map,
    Stream,
    Sequence
};
use PHPUnit\Framework\TestCase;

class SetTooShortTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StrategyDeterminator::class,
            new SetTooShort(Map::of(DefinitionSet::class, Strategy::class))
        );
    }

    public function testThrowWhenInvalidMapKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<Innmind\Math\DefinitionSet\Set, Innmind\Homeostasis\Strategy>');

        new SetTooShort(Map::of('string', Strategy::class));
    }

    public function testThrowWhenInvalidMapValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<Innmind\Math\DefinitionSet\Set, Innmind\Homeostasis\Strategy>');

        new SetTooShort(Map::of(DefinitionSet::class, 'string'));
    }

    public function testDefaultStrategy()
    {
        $determinate = new SetTooShort(Map::of(DefinitionSet::class, Strategy::class));

        $strategy = $determinate(Sequence::of(State::class));

        $this->assertSame(Strategy::holdSteady(), $strategy);
    }

    public function testThrowWhenStatesTooLong()
    {
        $determinate = new SetTooShort(Map::of(DefinitionSet::class, Strategy::class));
        $state = new State(
            $this->createMock(PointInTime::class),
            Map::of('string', Measure::class)
                (
                    'cpu',
                    new Measure(
                        $this->createMock(PointInTime::class),
                        new Number(1),
                        new Weight(new Number(1))
                    )
                )
        );
        $states = Sequence::of(State::class, $state, $state, $state, $state, $state);

        $this->expectException(StrategyNotDeterminable::class);

        $determinate($states);
    }

    /**
     * @dataProvider states
     */
    public function testStrategy($expected, $state)
    {
        $determinate = new SetTooShort(
            Map::of(DefinitionSet::class, Strategy::class)
                (
                    new Range(true, new Number(0), new Number(0.2), false),
                    Strategy::dramaticIncrease()
                )
                (
                    new Range(true, new Number(0.2), new Number(0.4), false),
                    Strategy::increase()
                )
                (
                    new Range(true, new Number(0.4), new Number(0.6), true),
                    Strategy::holdSteady()
                )
                (
                    new Range(false, new Number(0.6), new Number(0.8), true),
                    Strategy::decrease()
                )
                (
                    new Range(false, new Number(0.8), new Number(1), true),
                    Strategy::dramaticDecrease()
                )
        );

        $strategy = $determinate(
            Sequence::of(State::class, $state)
        );

        $this->assertSame($expected, $strategy);
    }

    public function states(): array
    {
        $values = [
            [Strategy::dramaticIncrease(), 0.1],
            [Strategy::increase(), 0.3],
            [Strategy::holdSteady(), 0.5],
            [Strategy::decrease(), 0.7],
            [Strategy::dramaticDecrease(), 0.9],
        ];

        foreach ($values as &$value) {
            $value[1] = new State(
                $this->createMock(PointInTime::class),
                Map::of('string', Measure::class)
                    (
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTime::class),
                            new Number($value[1]),
                            new Weight(new Number(1))
                        )
                    )
            );
        }

        return $values;
    }
}
