<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator\SetTooShort,
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    State\Identity,
    Sensor\Measure,
    Sensor\Measure\Weight
};
use Innmind\Math\{
    DefinitionSet\Set as DefinitionSet,
    DefinitionSet\Range,
    Algebra\Number\Number
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Map,
    Set,
    SetInterface
};
use PHPUnit\Framework\TestCase;

class SetTooShortTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            StrategyDeterminator::class,
            new SetTooShort(new Map(DefinitionSet::class, Strategy::class))
        );
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\InvalidStrategies
     */
    public function testThrowWhenInvalidMapKey()
    {
        new SetTooShort(new Map('string', Strategy::class));
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\InvalidStrategies
     */
    public function testThrowWhenInvalidMapValue()
    {
        new SetTooShort(new Map(DefinitionSet::class, 'string'));
    }

    public function testDefaultStrategy()
    {
        $determinate = new SetTooShort(new Map(DefinitionSet::class, Strategy::class));

        $strategy = $determinate(new Set(State::class));

        $this->assertSame(Strategy::holdSteady(), $strategy);
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\StrategyNotDeterminable
     */
    public function testThrowWhenStatesTooLong()
    {
        $determinate = new SetTooShort(new Map(DefinitionSet::class, Strategy::class));
        $set = $this->createMock(SetInterface::class);
        $set
            ->expects($this->once())
            ->method('size')
            ->willReturn(5);

        $determinate($set);
    }

    /**
     * @dataProvider states
     */
    public function testStrategy($expected, $state)
    {
        $determinate = new SetTooShort(
            (new Map(DefinitionSet::class, Strategy::class))
                ->put(
                    new Range(true, new Number(0), new Number(0.2), false),
                    Strategy::dramaticIncrease()
                )
                ->put(
                    new Range(true, new Number(0.2), new Number(0.4), false),
                    Strategy::increase()
                )
                ->put(
                    new Range(true, new Number(0.4), new Number(0.6), true),
                    Strategy::holdSteady()
                )
                ->put(
                    new Range(false, new Number(0.6), new Number(0.8), true),
                    Strategy::decrease()
                )
                ->put(
                    new Range(false, new Number(0.8), new Number(1), true),
                    Strategy::dramaticDecrease()
                )
        );

        $strategy = $determinate(
            (new Set(State::class))->add($state)
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
                $this->createMock(Identity::class),
                $this->createMock(PointInTimeInterface::class),
                (new Map('string', Measure::class))
                    ->put(
                        'cpu',
                        new Measure(
                            $this->createMock(PointInTimeInterface::class),
                            new Number($value[1]),
                            new Weight(new Number(1))
                        )
                    )
            );
        }

        return $values;
    }
}
